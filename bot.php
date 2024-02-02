<?php
 
require __DIR__.'/../../vendor/autoload.php';
 
$path = "https://api.telegram.org/bot6917767497:AAG6KBo_4a4ytus6toj4k8_xGIpsGmcTJqg/";
$update = json_decode(file_get_contents("php://input"), TRUE); 

$chatId = $update["message"]["chat"]["id"];
$message =  $update["message"]["text"];
$first = $update["message"]["from"]["first_name"];

$last = $update["message"]["from"]["last_name"];
$callback_query = $update['callback_query'];


$horan_id=horanid($chatId, $first);
$user=user($horan_id);
$state = $user['state'];

// Translator 
require __DIR__.'/../../vendor/autoload.php';
use Stichoza\GoogleTranslate\GoogleTranslate;
$to = $user['lang'];
$tr = new GoogleTranslate();
$tr->setSource();
$tr->setTarget($to);

    
// Translate the texts for the buttons
$mainMenuText = $tr->translate('Main Menu');
$amharicText = $tr->translate('Amharic');
$englishText = $tr->translate('English');
$shareContactText = $tr->translate('Share Contact');
$buyAlbumText = $tr->translate('Buy Album');
$buyBookText = $tr->translate('Buy Book');
$changeLanguageText = $tr->translate('Change Language');

// ------------------------ Back Button -----------------------
$back = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => "🧾 Main Menu", 'callback_data' => 'back']
            ]
        ]
    ]);  

$lang = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => "🇪🇹 $amharicText", 'callback_data' => 'am'],
            ],
            [
                ['text' => "🇬🇧 $englishText", 'callback_data' => 'en'],
            ],
        ]
    ]);

$share_contact = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => "👤 $shareContactText", 'callback_data' => 'share_contact'],
            ]
        ]
    ]);

// ---------------------------- Menu Buttons-------------------
$print = json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => "🎵 $buyAlbumText", 'callback_data'=> 'buy_album']
            ],
            [
                ['text' => "📖 $buyBookText", 'callback_data'=> 'buy_book'],  
            ],
            [
                ['text' => "🌐 $changeLanguageText", 'callback_data'=> 'lang'], 
            ]
        ]
    ]);
// ************************   HOME      ****************************
 

if($message =="/start"){
     update_user("state", "start", $chatId); 
     
        $msg1 = array(
                'chat_id'=>$chatId, 
                'caption'=>$tr->translate("
🎧 <b>አልበም ለመግዛት ከታች ያለውን <i> አልበሙን ይግዙ</i> የሚለውን ወይም <i> ቋንቋ ይቅይሩ</i> የሚለውን ምልክት ይጫኑ</b> 👇"),
                'reply_markup' =>$print,
                  'disable_web_page_preview' => false,
                'parse_mode' => 'HTML', 
                'photo'=>new CURLFile("tekeblonal_cover.jpg"),
       );
       send("sendPhoto", $msg1);
       

  //  update_input("None", $chatId);
}

//************************   CHANGE BOT LANGUAGE  ****************************
 

if($message =="/change"){ 
    if($to=="am"){$current="Amharic"; }
    if($to=="en"){$current="English"; } 
 
    $cha= 
        array(
            'chat_id' => $chatId, 
            'text' =>"🔀 Input Language:\n \t\t\t\t\t\t\t\t\t \t - Automatically Detected\nℹ️ Current Output:  
                ".$current."\n\nSelect Output  Language 👇", 
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false, 'reply_markup' =>$print);
        send("sendMessage", $cha); 
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
        $filepath = "https://api.telegram.org/file/bot6917767497:AAG6KBo_4a4ytus6toj4k8_xGIpsGmcTJqg/" . $file_path;


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

//************************   SEND NAME **********************************
if ($message != "" && $message != "/cancel" && $message != "/start"  && $state == "name") {
    update_user("fname", $message, $chatId);
    
    $name = array(
        'chat_id' => $chatId,
        'text' => "✅ Registered!",
        'parse_mode' => 'HTML'
    );
    send("sendMessage", $name);

 $msg= 
        array(
            'chat_id' => $chatId, 
            'text' =>$tr->translate("እባክዎትን ከዚህ በታች ካሉት አማራጮች በአንዱ ስልክ ቁጥርዎን ይላኩ።
1.  በሚከተለው መልክ ፅፈው ይላኩ +2519xxxxxxxx/+2517xxxxxxxx ወይም 09xxxxxxxx/07xxxxxxxx 

አሁን የሚሰራውን ተግባር ለመሰረዝ /cancel ብለው ይላኩ"), 
            'parse_mode' => 'HTML', 
            'disable_web_page_preview' => false);
    send("sendMessage", $msg);  
     
  update_user("state", "phone", $chatId);
}

//************************   SHARE PHONE NUMBER **********************************
if ($message != "" && $message != "/cancel" && $message != "/start"  && $state == "phone") {
    update_user("phone", $message, $chatId);
     update_user("state", "none", $chatId);
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
    
    
    $joinchannel1= json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => 'ይቀላቀሉ 🎉 ', 'url' => 'https://t.me/+0Wp1W7jd0CY5ZjA0'],
            ],
        ]
    ]);
    
      $joinchannel2= json_encode(
    [
        'inline_keyboard' => [
            [
                ['text' => 'ይቀላቀሉ 🎉 ', 'url' => 'https://t.me/+CTxFX8WbHTMzYTFk'],
            ],
        ]
    ]);
    

        
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
      
        // You can also send a confirmation message to the admin
          $confirmationMsg = array(
             'chat_id' => $chatId,
            'text' => "🎉 Order is Confirmed ",
            'parse_mode' => 'HTML',
        );
        send("sendMessage", $confirmationMsg);
        
        $user2 = getUserByID($userID) ;
        if($user2['order_type'] == "book") {
            
              update_user("buy_book", "yes", $userID);
            
            $notificationR = array(
                'chat_id' => $userID,
                'text' => "ክፍያዎ በተሳካ ሁኔታ ተረጋግጦአል። ወደ ፕራይቬት ቻናሉ ከታች ያለውን በተን በመጫን ይግቡ።",
                'parse_mode' => 'HTML',
                'reply_markup' => $joinchannel2,
            );
            send("sendMessage", $notificationR);
              update_order($userID, "book");
            
        }
        else{
            
              update_user("buy_album", "yes", $userID);
            
            $notificationR = array(
                 'chat_id' => $userID,
                'text' => "ክፍያዎ በተሳካ ሁኔታ ተረጋግጦአል። ወደ ፕራይቬት ቻናሉ ከታች ያለውን በተን በመጫን ይግቡ።",
                'parse_mode' => 'HTML',
                'reply_markup' => $joinchannel1,
            );
            send("sendMessage", $notificationR);
              update_order($userID, "album");
        }
        
        
    }
    
    if ($data == "back"){
    

         $delete1=  array(
            'chat_id' => $chatId, 
            'message_id' =>$msg);
                send("deleteMessage", $delete1); 
         update_user("state", "start", $chatId); 
     
        $msg1 = array(
                'chat_id'=>$chatId, 
                'caption'=>$tr->translate("ወደ ማውጫ ለመመለስ /start የምለውን ይጫኑ 

👉 /start"),
               
                  'disable_web_page_preview' => false,
                'parse_mode' => 'HTML', 
                'photo'=>new CURLFile("tekeblonal_cover.jpg"),
       );
       send("sendPhoto", $msg1);
    }
     
    if($data=="buy_album"){
        
             $loading = array(
             'chat_id' => $chatId,
           'text' => $tr->translate("🙏 አልበሙን ለመግዛት ስለወሰኑ እናመሰግናለን፣ እባክዎ ከታች እንደተመሩት ይከተሉ።"),
            'parse_mode' => 'HTML',
        );
        send("sendMessage", $loading);
        
        
$msg= 
        array(
            'chat_id' => $chatId, 
            'text' =>$tr->translate("እባክዎትን ሙሉ ስሞትን ፅፈው ይላኩ\n
አሁን የሚሰራውን ተግባር ለመሰረዝ /cancel ብለው ይላኩ
"), 
            'parse_mode' => 'HTML', 
            'disable_web_page_preview' => false);
    send("sendMessage", $msg); 
     update_user("state", "name", $chatId);
     
     
    update_user("order_type", "album", $chatId);
   
    
}


   if($data=="buy_book"){
    $loading = array(
             'chat_id' => $chatId,
           'text' => $tr->translate("🙏 አልበሙን ለመግዛት ስለወሰኑ እናመሰግናለን፣ እባክዎ ከታች እንደተመሩት ይከተሉ።"),
            'parse_mode' => 'HTML',
        );
        send("sendMessage", $loading);
        
        
$msg= 
        array(
            'chat_id' => $chatId, 
            'text' =>$tr->translate("እባክዎትን ሙሉ ስሞትን ፅፈው ይላኩ\n
አሁን የሚሰራውን ተግባር ለመሰረዝ /cancel ብለው ይላኩ
"), 
            'parse_mode' => 'HTML', 
            'disable_web_page_preview' => false);
    send("sendMessage", $msg); 
    update_user("state", "name", $chatId);
     
    update_user("order_type", "book", $chatId); 
    
}

if($data=="en"){
        
        update_lang('en',$chatId);
      
        $tr->setTarget('en');
        $to = 'en';
        $edit= array(
             
            'chat_id' => $chatId, 
            'text' =>$tr->translate("✅ Successful!\nYour output language changed to: English\n\nወደ ማውጫ ለመመለስ /start የምለውን ይጫኑ 

👉 /start"),
            'message_id' =>$msg);
        send("editMessageText", $edit);
      
         
    }
if($data=="am"){
        
        update_lang('am',$chatId);
        
        $tr->setTarget('am');
          $to = 'am';
        $edit= array(
           
            'chat_id' => $chatId, 
            'text' =>$tr->translate("✅ Successful!\nYour output language changed to: Amharic\n\nወደ ማውጫ ለመመለስ /start የምለውን ይጫኑ 

👉 /start"),
            'message_id' =>$msg);
        send("editMessageText", $edit);
         
    }
    
  if($data=="lang"){
    if($to =="am"){$current="Amharic"; }
    if($to =="en"){$current="English"; }
    $cha= 
        array(
            'chat_id' => $chatId, 
             'text' =>"🔀 ️ Current Language:  
                ".$current."\n\nSelect Bot Language 👇", 
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false, 'reply_markup' =>$lang);
        send("sendMessage", $cha); 
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
        'text' =>("ከታች ባሉት የባንክ አማራጮች አንዱን መርጠው 200 ብር ያስተላልፉ። ከዚህም በኃላ የከፍሉበትን ባንክ ያስገቡበትን ደረሰኝ(screenshot) ፎቶ ይላኩ፡-

Please pay 200 Birr using one of the bank accounts below and send us the screenshot.

#Name: <b>Ethiopia Full Gospel Tewodros Square Full Gospel Building Construction

🏦 Account Numbers
CBE:  1000595710646
OR
Birhan Bank: 1600580011529  </b>

አሁን የሚሰራውን ተግባር ለመሰረዝ /cancel ብለው ይላኩ"),

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



function update_order($chatId, $type) {
    // Database connection
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    
    // Prepare statement to avoid SQL injection
    $stmt = $db->prepare("SELECT id FROM mezmur_album_orders WHERE chatid = ? AND type = ?");
    $stmt->bind_param("ss", $chatId, $type); // 'ss' denotes two string parameters
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $d = $result->fetch_assoc();
        $row_id = $d['id'];

        // Prepare statement for update
        $updateStmt = $db->prepare("UPDATE mezmur_album_orders SET state = 'paid' WHERE id = ?");
        $updateStmt->bind_param("i", $row_id); // 'i' denotes integer parameter
        $updateStmt->execute();

        if ($updateStmt->error) {
            // Handle error
        }
    } else {
        // Handle case where no record is found or query fails
    }

    // Close statements and connection
    $stmt->close();
    $updateStmt->close();
    $db->close();
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

// Nahom ID 'chat_id' => "6800563832", 
// Selam ID 313721341
// Zeri ID 343598731


// Send to Zeru  
$with_profile = [
        'chat_id' => "343598731",  // admin chat id
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



// Send to Selam  
$with_profile = [
        'chat_id' => "313721341",  // admin chat id
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

 
    
// Gemechis   for testing only  
$with_profile = [
        'chat_id' => "1468513798",  // admin chat id
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
    $url = "https://api.telegram.org/bot6917767497:AAG6KBo_4a4ytus6toj4k8_xGIpsGmcTJqg/".$method;
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

function getUserByID($userID){
    $db = mysqli_connect('localhost', 'gujisoft_root', 'Zwh~KuUAwIpU', 'gujisoft_bots');
    $user_check_query = "SELECT * FROM mezmur_album_bot WHERE chatid='$userID'";
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
        $query = "INSERT INTO mezmur_album_bot (chatid, fname, lang) VALUES ('$chatId','$first', 'am')";
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
    $url = "https://api.telegram.org/bot6917767497:AAG6KBo_4a4ytus6toj4k8_xGIpsGmcTJqg/".$method;
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