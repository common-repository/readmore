<?php
/*
Plugin Name: Read More Function
Description:  kuerzt Posts nach konfigurierbaren Einstellungen  (trim your Postings)
Version: 1.1
Author: complex economy GmbH
Author URI: https://www.complex-economy.de/
*/

add_action('admin_menu', 'add_admin_pages');
add_filter('the_posts', 'shorten_comment');

	
function add_admin_pages()
{
  add_options_page('Read More Options','Read More', 10,__FILE__, 'options_menu');
  add_option("rm_art_kuerzung");
  add_option("rm_anzahl_kuerzung");
  add_option("rm_linkname");
  add_option("rm_linkbezeichnung");
}

function options_menu()
{
if(isset($_POST["art_kuerzung"]))
  {
    if (is_numeric($_POST["anzahl_kuerzung"]))
    {
      update_option("rm_art_kuerzung",$_POST["art_kuerzung"]);
      update_option("rm_anzahl_kuerzung",$_POST["anzahl_kuerzung"]);
      update_option("rm_linkname",$_POST["linkname"]);
      update_option("rm_linkbezeichnung",$_POST["linkbezeichnung"]);
    }
    else
    {
       echo "Error! Ihre Eingabe ist nicht korrekt. Die Eingabe fuer die Anzahl der Kuerzung muss numerisch sein.";
    }
   // if ($_POST["rm_linkname"]!="benutzerdefiniert")
      {
    //    update_option("rm_linkbezeichnung","");
      }
  }
?>
  <div class="wrap">
  <h2>Read More Options</h2>
  <form action="" name="options" method="post">
  <p> Wie soll der Text gekuerzt werden?
    <select name="art_kuerzung" size="1">
      <?php
      if  (get_option("rm_art_kuerzung")=="Paragraphen")
      {
      echo "<option selected>Paragraphen</option>";
      }
      else {echo "<option>Paragraphen</option>";};
      if  (get_option("rm_art_kuerzung")=="Woerter")
      {
      echo "<option selected>Woerter</option>";
      }
      else {echo "<option>Woerter</option>";};
      if  (get_option("rm_art_kuerzung")=="Zeichen")
      {
      echo "<option selected>Zeichen</option>";
      }
      else {echo "<option>Zeichen</option>";};
      ?>
    </select>
  </p>
  <p>nach <input name="anzahl_kuerzung" type="text" size="6" maxlength="5" value="<?php echo get_option("rm_anzahl_kuerzung") ?>"> soll gekuerzt werden</p>
  <p> Name des Links
    <select name="linkname" size="1">
      <?php
      if  (get_option("rm_linkname")=="weiterlesen")
      {
      echo "<option selected>weiterlesen</option>";
      }
      else {echo "<option>weiterlesen</option>";};
      if  (get_option("rm_linkname")=="Titel")
      {
      echo "<option selected>Titel</option>";
      }
      else {echo "<option>Titel</option>";};
      if  (get_option("rm_linkname")=="benutzerdefiniert")
      {
      echo "<option selected>benutzerdefiniert</option>";
      }
      else {echo "<option>benutzerdefiniert</option>";};
      ?>
    </select>
  </p>
  <p> benutzerdefinierter Name des Links: <input name="linkbezeichnung" type="text" size="20" maxlength="40" value="<?php echo get_option("rm_linkbezeichnung") ?>"></p>
  <input type="submit" value=" Absenden ">
  <input type="reset" value=" Abbrechen">
  </form>
  </div>
 
<?php
}

function shorten_comment($posts)
{
  $art_kuerzung=get_option("rm_art_kuerzung");
  $anzahl_kuerzung=get_option("rm_anzahl_kuerzung");
  if ($art_kuerzung=="Zeichen"){$anzahl_kuerzung=--$anzahl_kuerzung;}
  $linkname=get_option("rm_linkname");
  $linkbezeichnung=get_option("rm_linkbezeichnung");
  
  for($i=0;$i<count($posts);$i++)
    {
      if (is_single())
        continue;
      
      if ($linkname=="weiterlesen")
        {
          $linkbezeichnung="weiterlesen";
        }
        elseif($linkname=="Titel")
          {
            $linkbezeichnung=$posts[$i]->post_title;
          }

      if ($art_kuerzung=="Paragraphen")
        {
        $content=explode("\r\n\r\n",$posts[$i]->post_content);        
        $posts[$i]->post_content="";
        for ($k=0;$k<$anzahl_kuerzung;$k++)
          {
             $posts[$i]->post_content.=$content[$k]."\r\n\r\n";
          }
         $posts[$i]->post_content .= ' <a href="'.$posts[$i]->guid.'">'.$linkbezeichnung.'</a>'; 
        }
        
      if ($art_kuerzung=="Woerter")     
        {
        $words=str_split($posts[$i]->post_content,1);
        $content_len=strlen($posts[$i]->post_content);
        $posts[$i]->post_content="";
        $anzahl_woerter=0;
        $in_wort=FALSE;
        for ($p=0;$p<$content_len;$p++)
           {
             if ($words[$p]==' ')                        
              {
                $posts[$i]->post_content.=' ';
                $in_wort=FALSE;
              }
             else
             {
                if ($in_wort==FALSE)
                  {
                    $anzahl_woerter=$anzahl_woerter+1;
                    $in_wort=TRUE;
                  }
                $posts[$i]->post_content.=$words[$p];
             }
           if ($anzahl_woerter>=$anzahl_kuerzung)  
              {
                switch($words[$p])
                  {
                  case ".":break 2;
                  case "!":break 2;
                  case "?":break 2;
                  case ":":break 2;
                  }                           
              }       
           }        
        $posts[$i]->post_content .= ' </p><p><a href="'.$posts[$i]->guid.'">'.$linkbezeichnung.'</a>'; 
        }
        
      if ($art_kuerzung=="Zeichen")
        {
          $characters=str_split ($posts[$i]->post_content,1);
          $content_len=strlen($posts[$i]->post_content) ;
          $posts[$i]->post_content="";
          $in_sentence=FALSE;
          for ($l=0;$l<$anzahl_kuerzung+1;$l++)
            {
              $posts[$i]->post_content.=$characters[$l];
            }
          $content=trim($posts[$i]->post_content);
          switch ($characters[strlen($content)-1])
          {
              case ".":break;
              case "!":break;
              case "?":break;
              case ":":break;
              default:
                if (strlen($content)!=strlen($posts[$i]->post_content))
                    {
                       $posts[$i]->post_content.=" ";
                    }
                for ($m=($anzahl_kuerzung+1);$m<$content_len;$m++)
                  {
                      switch ($characters[$m])
                        {
                           case ".":
                                $posts[$i]->post_content.='.';
                                break 2;
                           case "!":
                                $posts[$i]->post_content.='!';
                                break 2;
                           case "?":
                                $posts[$i]->post_content.='?';
                                break 2;
                           case ":":
                                $posts[$i]->post_content.=':';
                                break 2;     
                           case "\r\n":break 2;
                           default: $posts[$i]->post_content.=$characters[$m];                               
                        }                        
                  }
          }          
        $posts[$i]->post_content .= ' </p><p><a href="'.$posts[$i]->guid.'">'.$linkbezeichnung.'</a>';                                                
        }    
  	
    }
  return $posts;  
}                   
?>