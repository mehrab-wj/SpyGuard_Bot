<?php
$plugin_info = [
  "name"=>"Database handler",
  "description"=>"this plugin programmed for handle the database. ",
  "developer"=>"@OneProgrammer"
];

class DatabaseConnection {
  public $host = "localhost";
  public $username = "zirgozar";
  public $password = "**2289777**";
  public $dbname = "zirgozar_spyguard_bot";
  public $con;
  public $select_db;
  public $from_id = 0;
  function __construct($from_id = 0) {
    $this->con = mysqli_connect($this->host, $this->username, $this->password);
    if (!$this->con){
        die("Database Connection Failed :( <br>" . mysqli_error($this->con));
    }
    $this->select_db = mysqli_select_db($this->con, $this->dbname);
    if (!$this->select_db){
        die("Database Selection Failed :! <br>" . mysqli_error($this->con));
    }
    mysqli_query($this->con,"SET NAMES 'utf8mb4'");
    mysqli_query($this->con,"SET CHARACTER SET 'utf8mb4'");
    mysqli_query($this->con,"SET character_set_connection = 'utf8mb4'");
    $this->from_id = $from_id;
  }
  function is_admin($from_id) {
    $search_query = mysqli_query($this->con,"SELECT * FROM `admins` WHERE `user_id` = '$from_id'");
    if (mysqli_num_rows($search_query) >= 1) {
      return true;
    }
    else { return false; }
  }
  function get_message($tag) {
    $search_query = mysqli_query($this->con,"SELECT * FROM `messages` WHERE `tag` = '$tag'");
    return mysqli_fetch_assoc($search_query);
  }
  function check_message($tag) {
    $search_query = mysqli_query($this->con,"SELECT * FROM `messages` WHERE `tag` LIKE '$tag'");
    if (mysqli_num_rows($search_query) == 0) {return false;}
    else {return mysqli_fetch_assoc($search_query);}
  }
  function update_user($from_id,$name) {
    $search_query = mysqli_query($this->con,"SELECT * FROM `users` WHERE `user_id` = '$from_id'");
    if (mysqli_num_rows($search_query) >= 1) {
      mysqli_query($this->con,"UPDATE `users` SET `name` = '$name' WHERE `users`.`user_id` = '$from_id'; ");
      return "user_updated!";
    }
    else {
      mysqli_query($this->con,"INSERT INTO `users` (`id`, `user_id`, `name`, `blocked`, `step`) VALUES (NULL, '$from_id', '$name', 'none', 'none'); ");
      return "user created!";
    }
  }
  function set_step($from_id,$step) {
    $update_query = mysqli_query($this->con,"UPDATE `users` SET `step` = '$step' WHERE `users`.`user_id` = '$from_id'; ");
    if ($update_query) { return $update_query; }
    else { return false; }
  }
  function user($from_id) {
    $search_query = mysqli_query($this->con,"SELECT * FROM `users` WHERE `user_id` = '$from_id'");
    if (mysqli_num_rows($search_query) >= 1) {
      return mysqli_fetch_assoc($search_query);
    }
    else {return null;}
  }
  function get_option($tag,$default = "not set") {
    $search_query = mysqli_query($this->con,"SELECT * FROM `options` WHERE `tag` LIKE '$tag'");
    if (mysqli_num_rows($search_query) == 1) { $data = mysqli_fetch_assoc($search_query); return $data['value']; }
    elseif (mysqli_num_rows($search_query) > 1) { return "to many options was set!"; }
    else { return $default; }
  }
  function call_admin($textmessage) {
    $search_query = mysqli_query($this->con,"SELECT * FROM `admins`");
    $admins = [];
    while ($admin_info = mysqli_fetch_assoc($search_query)) {
      array_push($admins,"{$admin_info['name']} - [{$admin_info['user_id']}]");
    }
    if (in_array($textmessage,$admins)) { return true; }
    else { return false; }
  }
  function serialize_admin($textmessage) {
    $data = explode("-",$textmessage);
    $data[1] = str_replace("-","",$data[1]);
    $data[1] = str_replace(" [","",$data[1]);
    $data[1] = str_replace("]","",$data[1]);
    return $data[1];
  }
  function get_admin_info($from_id) {
    $search_query = mysqli_query($this->con,"SELECT * FROM `admins` WHERE `user_id` = '$from_id'");
    if (mysqli_num_rows($search_query) == 1) { return mysqli_fetch_assoc($search_query); }
    else {return false;}
  }
  function get_admin_list($type = "array") {
    $search_query = mysqli_query($this->con,"SELECT * FROM `admins`");
    if (mysqli_num_rows($search_query) >= 1) {
      if ($type = "keyboard") {
        $kb = [
          [
            ['text'=>"خانه 🏚"]
          ]
        ];
        $n = 1;
        while ($admin_info = mysqli_fetch_assoc($search_query)) {
            array_push($kb,[["text"=>"{$admin_info['name']} - [{$admin_info['user_id']}]"]]);
        }
        return $kb;
      }
      else {
        return mysqli_fetch_assoc($search_query);
      }
    }
    else {
      return false;
    }
  }
  function keyboard() {
    $panel_kb = [['text'=>""]];
    if ($this->is_admin($this->from_id)) {
      $panel_kb = [
        ['text'=>"پنل مدیریت"]
      ];
    }
    $keyboard = json_encode([
              	'keyboard'=>[
                    [
                       ['text'=>"ارتباط با یکی از اعضای تیم"]
                    ],
                    [
                       ['text'=>"ارتباط با کل تیم"]
                    ],
                    [
                      ['text'=>"مخزن در گیتهاب 🔆"],
                      ['text'=>"◀️ بیشتر"]
                    ],
                    $panel_kb
              	],
              	'resize_keyboard'=>true
         		]);

    return $keyboard;
  }
  function serialize_keyboard($array,$resize_keyboard = true) {
    $keyboard = json_encode([
              	'keyboard'=>
                    $array
              	,
              	'resize_keyboard'=>$resize_keyboard
         		]);
    return $keyboard;
  }
  function get_more_buttons() {
    $keyboard = [ ];
    $search_query = mysqli_query($this->con,"SELECT * FROM `messages` WHERE `keyboard` = 'true'");
    $n = 0;
    if (mysqli_num_rows($search_query) >= 1) {
      while ($kb_info = mysqli_fetch_assoc($search_query)) {
        $kb_array;
        if ($n == 0 || $n == 1) { $n++; }
        elseif ($n == 2) { $n = 0; }
        array_push($keyboard,[['text'=>$kb_info['tag']]]);
      }
    }
    return $this->serialize_keyboard($keyboard);
  }
}
 ?>
