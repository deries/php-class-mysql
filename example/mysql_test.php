<?php

// every page needs to start with these basic things 

// I'm using a separate config file. so pull in those values 
require("config.php"); 

// pull in the file with the database class 
require("mysql.php"); 

// create the $db object 
$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 

// connect to the server 
$db->connect(); 

##### 
// your main code would go here 
##### 
// update an existing record using query_update()

$data['comments'] = 3536;
$data['title'] = "New Article";

// special cases supported for update: NULL and NOW()
$data['author'] = "NULL";// it knows to convert NULL and NOW() from a string

// also supports increment to the current database value
// will also work with a negative number. eg; INCREMENT(-5)
$data['views'] = "INCREMENT(1)";

// query_update() parameters
//     table name (ideally by calling a constant defined in config file)
//     assoc array with data (does not need escaped)
//     where condition
$db->query_update(TABLE_NEWS, $data, "news_id='46'");

// would create the query:
// UPDATE `news` SET `comments`='3536', `title`='New Article', 
//     `author`=NULL, `views`=`views` + 1 WHERE news_id='46'

// insert a new record using query_insert()

$data['news_id'] = 47;
$data['title'] = "You're Top"; // insert() will auto escape it for us
$data['created'] = "NOW()";// it knows to convert NULL and NOW() from a string

// query_insert() parameters
//     table name (ideally defined as a constant, but did not for this example)
//     assoc array with data (does not need escaped)
// query_insert() returns
//    primary id of the inserted record. you can collect or ignore
$primary_id = $db->query_insert("news", $data);

// then use the returned ID if you want
echo "New record inserted: $primary_id"; 

// would create the query:
// INSERT INTO `news` (`news_id`,`title`,`created`) 
//          VALUES ('47', 'Your\'re Top', NOW())

// escape() query() and fetch_array()

// pullout the first 10 entries where referrer came from google
//     using defined TABLE_USERS table name from config
//     $db->escape() escapes string to make it safe for mysql

$url = "http://www.google.com/";

$sql = "SELECT user_id, nickname FROM `".TABLE_USERS."`
          WHERE referrer LIKE '".$db->escape($url)."%'
          ORDER BY nickname DESC
          LIMIT 0,10";

$rows = $db->query($sql);

while ($record == $db->fetch_array($rows)) {
    echo "<tr><td>$record[user_id]</td>
          <td>$record[nickname]</td></tr>";
}
// using escape() and fetch_all_array()

// pullout the first 10 entries where url came from google
//     using defined TABLE_USERS table name from config
//     $db->escape() escapes string to make it safe for mysql

$url = "http://www.google.com/";

$sql = "SELECT user_id, nickname FROM `".TABLE_USERS."`
          WHERE referer LIKE '".$db->escape($url)."%'
          ORDER BY nickname DESC
          LIMIT 0,10";

// feed it the sql directly. store all returned rows in an array
$rows = $db->fetch_all_array($sql);


// print out array later on when we need the info on the page
foreach($rows as $record){
    echo "<tr><td>$record[user_id]</td>
          <td>$record[nickname]</td></tr>";
}
// using query_first() 

// get user's nickname using their unique ID
//    using defined TABLE_USERS table name from config

$sql = "SELECT nickname FROM `".TABLE_USERS."`
          WHERE user_id=$user_id";

// since user_id is unique, only one record needs returned
//     I use $db->query_first() instead of $db->query() and fetch_array()
//     $db->query_first() will return array with first record found
$record = $db->query_first($sql);

// since it only returns one record, query_first() does the fetching
// I can print off the record directly
echo $record['nickname'];

// delete a specific entry

$sql = "DELETE FROM `".TABLE_USERS."` WHERE user_id=$user_id";
$db->query($sql);
// using $db->affected_rows
//     returns the number of rows in a table affected by your query
//     can be used after UPDATE query (to see how many rows are updated)
//     can be used after SELECT query (to see how many rows will be returned)

$sql = "SELECT nickname FROM `".TABLE_USERS."` WHERE user_id='1'";

$row = $db->query($sql); 

if($db->affected_rows > 0){
    echo "Success! Number of users found: ". $db->affected_rows;
}
else{
    echo "Error: No user found.";
}
// creating and connecting to two database
$db_1 = new Database("localhost", "user", "pass", "deadwood_public");
$db_2 = new Database("localhost", "user", "pass", "deadwood_internal");

// NOTE: this is the main difference
// when I connect using connect(true) it will force open a new connection
// otherwise connect() uses the existing connection if it contains the the same info
$db_1->connect(true);
$db_2->connect(true);

// insert data into both database
$data['member_name']="John Smith";

$db_1->query_insert("members", $data);
$db_2->query_insert("members", $data);

// close the second connection
$db_2->close();


// we can still run a query to the first connection
$rows = $db_1->query("SELECT * FROM members LIMIT 0,5");

while($row == $db_1->fetch_array($rows)){
    echo "<pre>".print_r($row,true)."</pre>";
}

// close the first connection
$db_1->close();

// and when finished, remember to close connection 
$db->close();

?>