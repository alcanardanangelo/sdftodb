<?php
/*
 * Only works on Windows based servers!
 * Prerequisite: SQL Server Compact Edition is installed on server (http://www.microsoft.com/en-us/download/details.aspx?id=5821)
 * Filename.....: class_sdf.php
 * Class........: sdf
 * Aufgabe......: open *.sdf MS SQL Compact Edition
 * written......: 14.10.2011
 * Written by Rainer Mohr (www.klemmkeil.de)
 * Modification of von class_mdb by Peter Klauer (knito.de)
 */

class sdf
{
  var $RS = 0;
  var $ADODB = 0;

  var $RecordsAffected;

  var $strProvider = 'Provider=Microsoft.SQLSERVER.CE.OLEDB.3.5;';
  // var $strProvider = 'Provider=Microsoft.SQLSERVER.CE.OLEDB.4.0;';
  var $strMode = '';
  var $strPSI = 'Persist Security Info=False';
  var $strDataSource = '';
  var $strConn = '';
  var $strRealPath = '';

  var $recordcount = 0;
  var $ok = false;

  /**
   * Constructor needs path to .sdf file
   *
   * @param string $dsn = path to *.sdf file
   * @param string $pass = Password of .sdf file, in case database is password protected
   * @return boolean success
   */
  function sdf($dsn = 'Please enter DataSource!', $pass = '')
  {
    $this->strRealPath = realpath($dsn);
    if (strlen($this->strRealPath) > 0) {
      $this->strDataSource = 'Data Source=' . $this->strRealPath;
      if (strlen($pass) > 0) $this->strDataSource .= '; SSCE:Database Password=' . $pass;
      $result = true;
    } else {
      echo "<br>sdf::sdf() File not found $dsn<br>";
      $result = false;
    }

    $this->open();

  } // eof constructor sdf()


  function open()
  {
    if (strlen($this->strRealPath) > 0) {

      $this->strConn =
        $this->strProvider . ';' .
        $this->strDataSource . ';' .
        $this->strMode . ';' .
        $this->strPSI;

      $this->ADODB = new COM('ADODB.Connection');

      if ($this->ADODB) {
        try {
          $this->ADODB->open($this->strConn);
          $result = true;
        } catch (Exception $e) {
          echo '<div class="warn">Exception: ' . $e . '</div><br>';
          $result = false;
        }
      } else {
        echo '<br>sdf::open() ERROR with ADODB.Connection<br>' . $this->strConn;
        $result = false;
      }
    }

    $this->ok = $result;

    return $result;

  } // eof open()


  /**
   * Execute SQL-Statement
   * @param string $strSQL = sql statement
   * @param boolean $getrecordcount = true when a record count is wanted
   */
  function execute($strSQL, $getrecordcount = false)
  {
    if ($this->ok == false) exit();

    $this->RS = $this->ADODB->execute($strSQL);

    if ($getrecordcount == true) {
      $this->RS->MoveFirst();
      $this->recordcount = 0;

      # brute force loop
      while ($this->RS->EOF == false) {
        $this->recordcount++;
        $this->RS->MoveNext();
      }
      $this->RS->MoveFirst();
    }
  } // eof execute()

  function eof()
  {
    return $this->RS->EOF;
  } // eof eof()

  function movenext()
  {
    $this->RS->MoveNext();
  } // eof movenext()

  function movefirst()
  {
    $this->RS->MoveFirst();
  } // eof movefirst()

  function close()
  {

    @$this->RS->Close(); // Generates a warning when without "@"
    $this->RS = null;

    @$this->ADODB->Close();
    $this->ADODB = null;
  } // eof close()

  function fieldvalue($fieldname)
  {
    return $this->RS->Fields[$fieldname]->value;
  } // eof fieldvalue()

  function fieldname($fieldnumber)
  {
    return $this->RS->Fields[$fieldnumber]->name;
  } // eof fieldname()

  function fieldcount()
  {
    return $this->RS->Fields->Count;
  } // eof fieldcount()


} // eoc sdf

?>