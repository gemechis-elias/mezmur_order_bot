<?php
 
require __DIR__.'/../../vendor/autoload.php';
 
$path = "https://api.telegram.org/bot6919832707:AAFYKk0RpgWI43_YakoOifJnj-QPFGON1rg/";
$update = json_decode(file_get_contents("php://input"), TRUE); 

$chatId = $update["message"]["chat"]["id"];
$message =  $update["message"]["text"];
$first = $update["message"]["from"]["first_name"];

$last = $update["message"]["from"]["last_name"];
$callback_query = $update['callback_query'];


$horan_id=horanid($chatId, $first);
$user=user($horan_id);
$state = $user['state'];


    
// ------------------------ Back Button -----------------------
$back = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => '🧾 Main Menu', 'callback_data' => 'back']
               
            ]
        ]
    ]);  
    


        
    
    
$share_contact = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => '👤 Share Contact ', 'callback_data' => 'share_contact'],
            
            ]
        ]
    ]);  
    
    

    
// ---------------------------- Menu Buttons-------------------
$print = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => 'አልበም ይግዙ ', 'callback_data'=> 'buy_album']
               
            ],
            [
                 ['text' => 'መጽሀፍ ይግዙ', 'callback_data'=> 'buy_book'],  
             ],
             [
                 
            ['text' => 'ቋንቋ ይቅይሩ', 'callback_data'=> 'change_lang'], 
            ]
           
        ]
    ]);


// ************************   HOME      ****************************
 

if($message =="/start"){
     update_user("state", "start", $chatId); 
     
        $msg1 = array(
                'chat_id'=>$chatId, 
                'caption'=>"
🎧 <b>Tekeblonal Album</b>
👤 AASTU ECSF Choir
💿 10 songs

አሁን ለመግዛት ከታች ያለውን ቁልፍ ይጫኑ፡",
                'reply_markup' =>$print,
                  'disable_web_page_preview' => false,
                'parse_mode' => 'HTML', 
                'photo'=>new CURLFile("tekeblonal_cover.jpg"),
       );
       send("sendPhoto", $msg1);
       

  //  update_input("None", $chatId);
}



// ************************ UPLOAD SCREENSHOT IMAGE ****************************
if (isset($update['message']['photo'][1]['file_id']) && $state == "photo") {
    $file_id = $update['message']['photo'][1]['file_id'];
    $geturl = $path . "getFile?file_id=" . $file_id;

    // Use cURL to get the file path JSON
    $ch = curl_init($geturl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $file_path_json = curl_exec($ch);

    if ($file_path_json === false) {
        // Handle cURL error
        $error_message = curl_error($ch);
        curl_close($ch);
        $wait = array(
            'chat_id' => $chatId,
            'text' => "cURL Error: " . $error_message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false
        );
        send("sendMessage", $wait);
        exit; // or return, depending on your application flow
    }

    curl_close($ch);

    $file_path_json = json_decode($file_path_json, true);

    if ($file_path_json && isset($file_path_json['result']['file_path'])) {
        $file_path = $file_path_json['result']['file_path'];
        $filepath = "https://api.telegram.org/file/bot6919832707:AAFYKk0RpgWI43_YakoOifJnj-QPFGON1rg/" . $file_path;


        send("sendMessage", $wait);
        // Validate image
        if (!isValidImage($filepath)) {
            // Continue with processing the image

            // Initialize a file URL to the variable
            $url = $filepath;
            $url = urldecode($url); // Decode the URL

            $file_name = basename($url);

            // Download the file and save it to a local directory using cURL
            try {
                $ch = curl_init($url);
                $fp = fopen('mezmur_album_screenshots/' . $file_name, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                // Open the downloaded image file
                $image = imagecreatefromjpeg('mezmur_album_screenshots/' . $file_name);

                // Save the image with the same quality as the original
                $date = date('Y-d-H-i-s');
                $name = $date . "-" . $file_name;
                imagejpeg($image, 'mezmur_album_screenshots/' . $name, 100);

                $id_url = 'https://www.horansoftware.com/bots/mezmur_album_screenshots/' . $name;
                echo "File downloaded successfully";
                add_new_order($id_url, $chatId, $name);
                
                send("sendMessage", $aaa1);
            } catch (Exception $e) {
                $wait = array(
                    'chat_id' => $chatId,
                    'text' => "Error: " . $e->getMessage(),
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => false
                );
                send("sendMessage", $wait);
            }
        } else {
            // Invalid image, send error message
            $wait = array(
                'chat_id' => $chatId,
                'text' => "Invalid image file. Please upload a valid image.",
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false
            );
            send("sendMessage", $wait);
        }
    } else {
        // Handle the case where $file_path_json is null or doesn't have the expected structure
        $wait = array(
            'chat_id' => $chatId,
            'text' => "Error: Invalid file path JSON.",
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false
        );
        send("sendMessage", $wait);
    }
}

function isValidImage($url)
{
    // Attempt to get image size
    $imageSize = @getimagesize($url);

    // Check if getimagesize was successful and the mime type is an image
    return $imageSize !== false && strpos($imageSize['mime'], 'image') === 0;
}

//************************   SHARE PHONE NUMBER **********************************
if ($message != "" && $message != "/cancel" && $message != "/start"  && $state == "phone") {
    update_user("phone", $message, $chatId);
    
    $phone = array(
        'chat_id' => $chatId,
        'text' => "✅Phone Number Registered!",
        'parse_mode' => 'HTML'
    );
    send("sendMessage", $phone);

 
     
    send_account_detail($chatId);
}


//************************   CANCEL OPRATION *************************************
if($message =="/cancel"){
 update_user("state", "none", $chatId);

  $cancel = array(
        'chat_id' => $chatId, 
        'text' =>"✅Opration is cancelled ", 
        'reply_markup' => $back,
        'parse_mode' => 'HTML', );
        send("sendMessage", $cancel); 
}

//************************   CALL BACK QUERY  ****************************
function extractUserIDFromCaption($caption) {
    $pattern = '/User ID: (\d+)$/'; // Match the pattern "User ID: " followed by digits at the end of the string
    preg_match($pattern, $caption, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

if(isset($update['callback_query'])){
    $data= $update['callback_query']['data'];
    $qid= $update['callback_query']['id'];
    $msg=$callback_query["message"]['message_id'];
    $chatId=$callback_query["message"]['chat']['id'];
    
    
    $joinchannel= json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => 'ይቀላቀሉ 🎉 ', 'url' => 'https://t.me/innovate_aastu'],
            ],
        ]
    ]);
    
     $loading = array(
             'chat_id' => $chatId,
            'text' => "Loading Please wait....",
            'parse_mode' => 'HTML',
        );
        send("sendMessage", $loading);
        
    // Assuming you have the user ID in the message caption
    $userID = extractUserIDFromCaption($callback_query["message"]['caption']);


    if ($data == "reject") {
     
       $notification = array(
             'chat_id' => $userID,
            'text' => "ይቅርታ ክፍያዎን ማረጋገጥ አልተቻለም! እባኮው በድጋሜ ይሞክሩ!"
,            'parse_mode' => 'HTML', 
        );
        send("sendMessage", $notification);
        
        
        
         $rejected = array(
             'chat_id' => $chatId,
            'text' => "The Order is Rejected.",
            'parse_mode' => 'HTML',
        );
        send("sendMessage", $rejected);
        
    }
     
     
    if ($data == "confirm") {
        // Update user
        update_user("buy_album", "yes", $userID);
        // You can also send a confirmation message to the admin
          $confirmationMsg = array(
             'chat_id' => $chatId,
            'text' => "🎉 Order is Confirmed ",
            'parse_mode' => 'HTML',
        );
        send("sendMessage", $confirmationMsg);
        
        $notificationR = array(
             'chat_id' => $userID,
            'text' => "ክፍያዎ በተሳካ ሁኔታ ተረጋግጦአል። ወደ ፕራይቬት ቻናሉ ከታች ያለውን በተን በመጫን ይግቡ።",
            'parse_mode' => 'HTML',
            'reply_markup' => $joinchannel,
        );
        send("sendMessage", $notificationR);
        
        
    }
    
     
    if($data=="buy_album"){
$msg= 
        array(
            'chat_id' => $chatId, 
            'text' =>"እባክዎትን ከዚህ በታች ካሉት አማራጮች በአንዱ ስልክ ቁጥርዎን ይላኩ።
1.  የመለያ ስልክ ቁጥርዎን ለማጋራት ከታች \"Share contact\" የሚለውን ቁልፍ ይጫኑ ወይም 
2.  በሚከተለው መልክ ፅፈው ይላኩ +2519xxxxxxxx/+2517xxxxxxxx ወይም 09xxxxxxxx/07xxxxxxxx 

አሁን የሚሰራውን ተግባር ለመሰረዝ /cancel ብለው ይላኩ", 
            'parse_mode' => 'HTML', 
            'disable_web_page_preview' => false);
    send("sendMessage", $msg); 
    update_user("order_type", "album", $chatId);
    update_user("state", "phone", $chatId);
    
}


   if($data=="buy_book"){
$msg= 
        array(
            'chat_id' => $chatId, 
            'text' =>"እባክዎትን ከዚህ በታች ካሉት አማራጮች በአንዱ ስልክ ቁጥርዎን ይላኩ።
1.  የመለያ ስልክ ቁጥርዎን ለማጋራት ከታች \"Share contact\" የሚለውን ቁልፍ ይጫኑ ወይም 
2.  በሚከተለው መልክ ፅፈው ይላኩ +2519xxxxxxxx/+2517xxxxxxxx ወይም 09xxxxxxxx/07xxxxxxxx 

አሁን የሚሰራውን ተግባር ለመሰረዝ /cancel ብለው ይላኩ", 
            'parse_mode' => 'HTML', 
            'disable_web_page_preview' => false);
    send("sendMessage", $msg); 
    update_user("order_type", "book", $chatId);
    update_user("state", "phone", $chatId);
    
}
}


        


 


//************************   BACK END   ****************************
function send_confirmation($chatId){ 
    $join = json_encode(
        [
            'inline_keyboard' => [
                [
                    ['text' => 'Join Channel', 'callback_data' => 'back']
                   
                ]
            ]
        ]);  
        
   $cha= 
    array(
        'chat_id' => $chatId, 
        'text' =>"ክፍያዎ በተሳካ ሁኔታ ተረጋግጦአል። ወደ ፕራይቬት ቻናሉ ከታች ያለውን በተን በመጫን ይግቡ።",  
        'parse_mode' => 'HTML',
        'reply_markup' => $join,
        'disable_web_page_preview' => true,);
    send("sendMessage", $cha);
}


function send_wating_msg($chatId){
     $msg = array(
        'chat_id' => $chatId, 
        'text' =>"ስክሪን ሾትዎ ለ ማረጋገጫ ተልኳል

እናመሰግናለን። እንዳረጋገጥን ኖቲፊኬሽን ይደርስዎታል።", 

        'parse_mode' => 'HTML', );

        send("sendMessage", $msg ); 
}

function send_account_detail($chatId){
    
     $msg = array(
        'chat_id' => $chatId, 
        'text' =>"ከታች ባሉት የባንክ አማራጮች አንዱን መርጠው 200 ብር ያስተላልፉ። ከዚህም በኃላ የከፍሉበትን ባንክ ያስገቡበትን ደረሰኝ(screenshot) ፎቶ ይላኩ፡-
<b>1. የአቢሲንያ ባንክ</b>
አበባይ ሺበሺ
አካውንት ቁጥር፡ 21348384

<b>2. ብርሃን ባንክ</b>
ጥሩሰው ሺበሺ
አካውንት ቁጥር፡ 1010824471510

<b>3. የኢትዮጵያ ንግድ ባንክ</b>
ሸዋንግዛው ሺበሺ
አካውንት ቁጥር፡ 1000160753092

<b>4. ዳሽን ባንክ</b>
አበባይ ሺበሺ
አካውንት ቁጥር፡ 5306799319021

<b>5. ደቡብ ግሎባል ባንክ</b>
ጥሩሰው ሺበሺ
አካውንት ቁጥር፡ 1697311699011

<b>6. አንበሳ ባንክ</b>
ሸዋንግዛው ሺበሺ
አካውንት ቁጥር፡ 00310145999

<b>7. ቴሌብር </b>
ሸዋንግዛው ሺበሺ
አካውንት ቁጥር፡ +251919912366

አሁን የሚሰራውን ተግባር ለመሰረዝ /cancel ብለው ይላኩ", 

        'parse_mode' => 'HTML', );

        send("sendMessage", $msg );  
      update_user("state", "photo", $chatId);
}


function update_user($key, $value, $chatId){
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $user_check_query = "SELECT id FROM mezmur_album_bot WHERE chatid='$chatId'";
    $result = mysqli_query($db, $user_check_query);
    $d = mysqli_fetch_assoc($result);
    $row_id= $d['id'];
    $update="UPDATE mezmur_album_bot SET $key='$value' WHERE id='$row_id' ";
    mysqli_query($db, $update);
}




function add_new_order($image, $chatId, $image_name){
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $user_check_query = "SELECT * FROM mezmur_album_bot WHERE chatid='$chatId'";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);
    
    $phone = $user['phone'];
    $type = $user['order_type'];
    $name =$user['fname'];
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $query2 = "INSERT INTO mezmur_album_orders (chatid,name,phone,type,image) VALUES('$chatId','$name', '$phone', '$type', '$image')";
    mysqli_query($db, $query2);
    
    sendAdmin($chatId, $name, $phone, $type, $image_name, $chatId);
   // send_confirmation($chatId);
   send_wating_msg($chatId);
}

function sendAdmin($chatId, $name, $phone, $type, $image, $userID) {
$confirm = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => '✅ Confirm ', 'callback_data' => 'confirm'],
                ['text' => '❌ Reject', 'callback_data' => 'reject'],
            ],
        ]
    ]);

    $photo_id = "mezmur_album_screenshots/$image";
    $date = date('Y-m-d');
    $with_profile = [
        'chat_id' => "6800563832",  // admin chat id
        'caption' => "🎉 New Order From User
Name:  $name
Phone: $phone
Order Type: $type
Date: $date
User ID: $userID",
        'parse_mode' => 'HTML',
        'reply_markup' => $confirm,
        'disable_web_page_preview' => false,
        
        'photo' => new CURLFile($photo_id),
    ];

    send2("sendPhoto", $with_profile);
}


function send2($method, $data)
{  
    $url = "https://api.telegram.org/bot6919832707:AAFYKk0RpgWI43_YakoOifJnj-QPFGON1rg/".$method;
    if (!$curld = curl_init()) {
        exit;
    }
    curl_setopt($curld, CURLOPT_POST, true);
    curl_setopt($curld, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curld, CURLOPT_URL, $url);
    curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curld);
    curl_close($curld);
    return $output;
}


//************************   BACK END   ****************************
function update_lang($value, $chatId){
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $user_check_query = "SELECT id FROM mezmur_album_bot WHERE chatid='$chatId'";
    $result = mysqli_query($db, $user_check_query);
    $d = mysqli_fetch_assoc($result);
    $row_id= $d['id'];
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $update="UPDATE mezmur_album_bot SET lang='$value' WHERE id='$row_id' ";
    mysqli_query($db, $update);
}


function user($hid){
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $user_check_query = "SELECT * FROM mezmur_album_bot WHERE id='$hid'";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);
    return $user;
}





function horanid($chatId,$first){
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $user_check_query = "SELECT id FROM mezmur_album_bot WHERE chatid='$chatId'";
    $result = mysqli_query($db, $user_check_query);
    $d = mysqli_fetch_assoc($result);
    $found= $d['id'];
    if($found!=""){return $found;}
    
    else{
        $query = "INSERT INTO mezmur_album_bot (chatid, fname) VALUES ('$chatId','$first')";
        mysqli_query($db, $query);
        if($chatId!=""){
            
        }
        $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
        $user_check_query = "SELECT id FROM mezmur_album_bot WHERE chatid='$chatId'";
        $result = mysqli_query($db, $user_check_query);
        $d = mysqli_fetch_assoc($result);
        $found= $d['id'];
        return $found;
    }
   
}



function send($method, $data)
{  
    $url = "https://api.telegram.org/bot6919832707:AAFYKk0RpgWI43_YakoOifJnj-QPFGON1rg/".$method;
    if (!$curld = curl_init()) {
        exit;
    }
    curl_setopt($curld, CURLOPT_POST, true);
    curl_setopt($curld, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curld, CURLOPT_URL, $url);
    curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curld);
    curl_close($curld);
    return $output;
}