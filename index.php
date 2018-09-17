<?php
/**
 * Sample implementation of converting SQL CE file to SQLite database file.
 */

include("class_sdf.php");

$path = "uploads/";

if ($handle = opendir($path)) {
  /**
   * Loop thru all files on upload folder
   */
  while (false !== ($file = readdir($handle))) {
    if ('.' === $file) continue;
    if ('..' === $file) continue;

    /**
     * Create the SQLite file.
     */
    $file_name = str_replace('.sdf', '', $file);
    $file_db = new PDO('sqlite:' . $file_name . '.db');
    $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /**
     * Create connection to MS SQL CE
     */
    $sdf = new sdf($path . $file, 'your_password_here');

    /**
     * You are now connected to the SQL CE file.
     * You can now perform queries.
     */
    $sdf->execute("SELECT * FROM Customers");

    /**
     * Construct data structure.
     */
    $data_array = [
      'customer_code' => NULL,
      'customer_name' => NULL,
    ];

    $customers = [];

    while (!$sdf->eof()) {
      $customers[$sdf->fieldvalue('customer_code')] = $data_array;
      $customers[$sdf->fieldvalue('customer_code')]['customer_code'] = $sdf->fieldvalue('customer_code');
      $customers[$sdf->fieldvalue('customer_code')]['customer_name'] = $sdf->fieldvalue('customer_name');
      $sdf->movenext();
    }

    /**
     * We will now create and insert data to our SQLite database file.
     */
    $file_db->exec("CREATE TABLE IF NOT EXISTS Customers (
              customer_code NVARCHAR(10), 
              customer_name NVARCHAR(10))");

    $insert = "INSERT INTO Customers(customer_code, customer_name) 
                VALUES (:customer_code, :customer_name)";

    $stmt = $file_db->prepare($insert);

    $stmt->bindParam(':customer_code', $customer_code);
    $stmt->bindParam(':customer_name', $customer_name);

    foreach ($customers as $value) {
      $customer_code = $value['customer_code'];
      $customer_name = $value['customer_name'];
      $stmt->execute();
    }

    $sdf->close();

    // After the conversion, you can redirect to other page or show a message.
  }
  closedir($handle);
}