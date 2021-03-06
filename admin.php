<?php
defined('SMILIES_PATH') or die('Hacking attempt!');

include_once(SMILIES_PATH.'include/functions.inc.php');

global $conf, $template;

// get available sets
$sets = array();
$handle = opendir(SMILIES_DIR);
while ($folder = readdir($handle))
{ 
  if ($folder != '.' && $folder != '..' && is_dir(SMILIES_DIR.$folder))
  {
    if (file_exists(SMILIES_DIR . $folder . '/representant.txt'))
    {
      $sets[$folder] = file_get_contents(SMILIES_DIR . $folder . '/representant.txt');
    }
    else
    {
      $sets[$folder] = get_first_file(SMILIES_DIR . $folder, $conf['smiliessupport_ext']);
    }
  }
}
closedir($handle);


// save configuration
if (isset($_POST['submit']))
{
  // new configuration
  $conf['smiliessupport'] = array(
    'folder' =>       $_POST['folder'],
    'cols' =>         preg_match('#^[0-9]+$#', $_POST['cols']) ? $_POST['cols'] : 6,
    'representant' => $sets[ $_POST['folder'] ],
  );
  
  conf_update_param('smiliessupport', $conf['smiliessupport']);
  $page['infos'][] = l10n('Information data registered in database');
  
  // shortcuts file
  $used = array();
  $content = null;
  
  foreach ($_POST['shortcuts'] as $file => $data)
  {
    if (empty($data)) continue;
    
    $data = explode(',', stripslashes($data));
    foreach ($data as $short)
    {
      if (array_key_exists($short, $used))
      {
        $page['errors'][] = l10n(
          '<i>%s</i>, shortcut &laquo; %s &raquo; already used for <i>%s</i>',
          get_filename_wo_extension($file),
          $short,
          get_filename_wo_extension($used[ $short ])
          );
      }
      else
      {
        $used[ $short ] = $file;
        $content.= $short."\t\t".$file."\n";
      }
    }
  }
  
  if (@!file_put_contents(SMILIES_DIR.$_POST['folder'].'/smilies-custom.txt', $content))
  {  
    $page['errors'][] = l10n('File/directory read error').' : '.SMILIES_DIR.$_POST['folder'].'/smilies-custom.txt';
  }
}


// template
$template->assign(array(
  'FOLDER' =>       $conf['smiliessupport']['folder'],
  'COLS' =>         $conf['smiliessupport']['cols'],
  'SETS' =>         $sets,
  'SMILIES_PATH' => SMILIES_PATH,
  ));


$template->set_filename('smiliessupport_conf', realpath(SMILIES_PATH . 'template/smiliessupport_admin.tpl'));
$template->assign_var_from_handle('ADMIN_CONTENT', 'smiliessupport_conf');
