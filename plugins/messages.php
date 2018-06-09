<?php
$plugin_info = [
  "name"=>"SpyGuard Message handler",
  "description"=>"this plugin programmed for handle the messages. ",
  "developer"=>"@OneProgrammer"
];

$db = new DatabaseConnection($from_id);
$step = $db->user($from_id)['step'];
$db->update_user($from_id,$name);

if ($textmessage == "خانه 🏚") {
  $db->set_step($from_id,"none");
  SendMessage($chat_id,$db->get_message($textmessage)["message"],null,"MarkDown",$db->keyboard());
}

//---------------- user step handling ....

elseif ($step == "Talk_wth_team") {
  Forward($db->get_option('group_id'),$chat_id,$message_id);
  $keyboard = [
    [
      ['text'=>"خانه 🏚"]
    ]
  ];
  $keyboard = $db->serialize_keyboard($keyboard);
  SendMessage($chat_id,$db->check_message("user_success_message")["message"],$message_id,"MarkDown",$keyboard);
}
elseif (strpos($step,"inTalk") !== false) {
  $admin_id = explode("_",$step);
  Forward($admin_id[1],$chat_id,$message_id);
  $keyboard = [
    [
      ['text'=>"خانه 🏚"]
    ]
  ];
  $keyboard = $db->serialize_keyboard($keyboard);
  SendMessage($chat_id,$db->check_message("user_success_message")["message"],$message_id,"MarkDown",$keyboard);
}
//----------------                    ....
elseif ($textmessage == "/start") {
  SendMessage($chat_id,$db->get_message("start_msg")["message"],null,"MarkDown",$db->keyboard());

}
elseif ($textmessage == "برای شروع چت اینجا کلیک کنید") {
  $step = str_replace("start","in",$step);
  $db->set_step($from_id,$step);
  $keyboard = [
    [
      ['text'=>"خانه 🏚"]
    ]
  ];
  $keyboard = $db->serialize_keyboard($keyboard);
  SendMessage($chat_id,$db->check_message($textmessage)["message"],null,"MarkDown",$keyboard);
}
elseif ($textmessage == "ارتباط با یکی از اعضای تیم") {

  $keyboard = $db->serialize_keyboard($db->get_admin_list("keyboard"));
  SendMessage($chat_id,"یکی از اعضای گروه رو انتخاب کن :",null,"MarkDown",$keyboard);
}
elseif ($textmessage == "ارتباط با کل تیم") {
  $db->set_step($from_id,"Talk_wth_team");
  $keyboard = [
    [
      ['text'=>"خانه 🏚"]
    ]
  ];
  $keyboard = $db->serialize_keyboard($keyboard);
  SendMessage($chat_id,$db->check_message($textmessage)["message"],null,"MarkDown",$keyboard);
}
elseif ($textmessage == "/hidekb") {
  makereq("SendMessage",[
    'chat_id'=>$chat_id,
    'text'=>"کیبرد مخفی شد .",
    'keyboard'=>json_encode([
      'ReplyKeyboardRemove'=>[
        "remove_keyboard"=>true,
        "selective"=>false
      ]
    ])
  ]);
}
elseif ($textmessage == "◀️ بیشتر") {
  $keyboard = $db->get_more_buttons();
  SendMessage($chat_id,$db->check_message($textmessage)["message"],null,"MarkDown",$keyboard);
}
elseif ($db->call_admin($textmessage)) {
  $admin_id = $db->serialize_admin($textmessage);
  $db->set_step($from_id,"startTalk_".$admin_id);
  $keyboard = [
    [
      ['text'=>"برای شروع چت اینجا کلیک کنید"]
    ],
    [
      ['text'=>"خانه 🏚"]
    ]
  ];
  $keyboard = $db->serialize_keyboard($keyboard);
  $admin = $db->get_admin_info($admin_id);
  SendMessage($chat_id,"نام : `{$admin['name']}`
بیوگرافی :
`{$admin['bio']}`",null,"MarkDown",$keyboard);
}
elseif ($db->check_message($textmessage)) {
  SendMessage($chat_id,$db->check_message($textmessage)["message"],null,"HTML",$db->keyboard());
}
else {
  if ($db->is_admin($from_id)) {
    if ($reply != null) {
      if ($textmessage == "/info") {
        $name = $update->message->reply_to_message->forward_from->first_name;
        SendMessage($chat_id,"user id : `$reply`\nname : [$name](tg://user?id=$reply)",$message_id);
      }
      else {
        SendMessage($reply,"[$name](tg://user?id=$from_id) :\n".$textmessage);
        SendMessage($chat_id,$db->get_message("admin_success_message")["message"],$message_id);
      }
    }
    else {
      SendMessage($chat_id,"دستور وارد شده معتبر نیست !");
    }
  }
  else {
    /* if ($forward == null) {
      Forward(66443035,$chat_id,$message_id);
      SendMessage($chat_id,$db->get_message("user_success_message")["message"],$message_id);
    }
    else {
      SendMessage($chat_id,$db->get_message("forward_blocked")["message"],$message_id);
    } */
    SendMessage($chat_id,"دستور وارد شده معتبر نیست !");
  }
}
?>
