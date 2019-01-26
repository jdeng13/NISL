<?php
/* 
   Relative to Absolute URLs
   Version 1.0
   August 8, 2016

   Will Bontrager Software LLC
   https://www.willmaster.com/
   Copyright 2016 Will Bontrager Software LLC

   This software is provided "AS IS," without 
   any warranty of any kind, without even any 
   implied warranty such as merchantability 
   or fitness for a particular purpose.
   Will Bontrager Software LLC grants 
   you a royalty-free license to use or 
   modify this software provided this 
   notice appears on all copies. 
*/
$Processed = false;
$Errors = $Messages = array();
foreach( $_POST as $k => $v ) { $_POST[$k] = stripslashes(trim($v)); }
if( isset($_POST['submitter']) )
{
   if( empty($_POST['baseURL']) or empty($_POST['fileName']) )
   {
      $Errors[] = 'Both the base URL and the web page file name need to be provided.';
   }
   if( ! preg_match('!^https?://.!',$_POST['baseURL']) )
   {
      $Errors[] = 'The base URL needs to be an http://... or https://... URL.';
   }
   if( ! file_exists($_POST['fileName']) )
   {
      $Errors[] = "The file {$_POST['fileName']} doesn't exist in the directory where this software is running.";
   }
   if( ! count($Errors) )
   {
      $page = '';
      if( ! ($page = file_get_contents($_POST['fileName'])) )
      {
         $Errors[] = "Unable to read file {$_POST['fileName']}";
         return;
      }
      $backupfile = time() . "_{$_POST['fileName']}";
      if( file_put_contents($backupfile,$page) )
      {
         $Messages[] = "Backup of original file is $backupfile";
      }
      else
      {
         $Errors[] = "Unable to create backup file $backupfile &mdash; verify this directory has sufficient write permissions.";
         return;
      }
      preg_match_all('!src=[\'"]?([^\'"\s]*)!si',$page,$src);
      foreach( $src[1] as $item )
      {
         if( preg_match('!^[a-zA-Z]+\:!',$item) ) { continue; }
         if( preg_match('!^\#\?!',$item) ) { continue; }
         extract(parse_url($_POST['baseURL']));
         if( strpos($item,'/')===0 ) { $path = ''; }
         if( $path ) { $path = preg_replace('!/[^/]*$!','',$path); }
         $abs = "$host$path/$item";
         while( preg_match('!/\w+/\.\./!',$abs) ) { $abs = preg_replace('!/\w+/\.\./!','/',$abs,1); }
         $abs = preg_replace('!/\.{0,2}/!','/',$abs);
         $item = preg_quote($item,'/');
         $page = preg_replace("!src=(['\"])?$item!i","src=\$1$scheme://$abs",$page);
      }
      preg_match_all('!href=[\'"]?([^\'"\s]*)!si',$page,$href);
      foreach( $href[1] as $item )
      {
         if( preg_match('!^[a-zA-Z]+\:!',$item) ) { continue; }
         if( preg_match('!^\#\?!',$item) ) { continue; }
         extract(parse_url($_POST['baseURL']));
         if( strpos($item,'/')===0 ) { $path = ''; }
         if( $path ) { $path = preg_replace('!/[^/]*$!','',$path); }
         $abs = "$host$path/$item";
         while( preg_match('!/\w+/\.\./!',$abs) ) { $abs = preg_replace('!/\w+/\.\./!','/',$abs,1); }
         $abs = preg_replace('!/\.{0,2}/!','/',$abs);
         $item = preg_quote($item,'/');
         $page = preg_replace("!href=(['\"])?$item!i","href=\$1$scheme://$abs",$page);
      }
      if( file_put_contents($_POST['fileName'],$page) )
      {
         $Messages[] = "Webpage file {$_POST['fileName']} has been processed and updated.";
         $Processed = true;
      }
      else
      {
         $Errors[] = "Unable to update the file {$_POST['fileName']}";
      }
   }
}
?><!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Relative to Absolute URLs</title>
<style type="text/css">
body { font-size:100%; font-family:sans-serif; }
#content { max-width:5.5in; margin:.75in auto; position:relative; }
input { width:100%; box-sizing:border-box; font-size:1em; }
input[type="text"] { padding:.25em; border:1px solid #ccc; border-radius:.25em; }
a { text-decoration:none; }
</style>
</head>
<body>
<div id="content">
<a href="//www.willmaster.com/">
<img src="//www.willmaster.com/images/wmlogo_icon.gif" style="width:50px; height:50px; position:absolute; left:-.25in; top:-.75in; border:none; outline:none;">
</a>
<h1>
Relative to Absolute URLs
</h1>
<?php if( count($Errors) ): ?>
<div style="border:3px solid red; padding:1em; border-radius:1em;">
<p style="margin-top:0; font-weight:bold;">
Notice:
</p>
<ul style="margin-bottom:0;"><li>
<?php echo( implode('</li><li>',$Errors) ) ?>
</li>
</ul>
</div>
<?php elseif( $Processed ): ?>
   <?php if( count($Messages) ): ?>
<p>&bull;
<?php echo( implode('</p><p>&bull; ',$Messages) ) ?>
</p>
   <?php endif; ?>
<p>
Do another?
</p>
<?php endif; ?>

<h3>
&mdash; Setup &mdash;
</h3>
<form method="post" enctype="multipart/form-data" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
<p>
The base URL.<br>
<input type="text" name="baseURL" required placeholder="The URL that relative links are relative to." value="<?php echo(@$_POST['baseURL']) ?>">
</p>
<p>
File name of web page to update.<br>
<input type="text" name="fileName" required placeholder="The file name of the web page to update." value="<?php echo(@$_POST['fileName']) ?>">
</p>
<p>
<input type="submit" name="submitter" value="Update Web Page File">
</p>
</form>
<p>Copyright 2016 <a href="//www.willmaster.com/">Will Bontrager Software LLC</a></p>
</div>
</body>
</html>
