<?php
/* For licensing terms, see /license.txt */
/**
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Chamilo Module. It can be inserted on any
 * Chamilo Module, provided a connection to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction"
 * in the course Database. Each module introduction has an Id stored on
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   intro_text :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
 *
 *	@package chamilo.include
 */

require_once api_get_path(LIBRARY_PATH).'thematic.lib.php';

/*	Constants and variables */

$TBL_INTRODUCTION = Database::get_course_table(TABLE_TOOL_INTRO);
$intro_editAllowed = $is_allowed_to_edit;
$session_id = api_get_session_id();

$introduction_section = '';

global $charset;
$intro_cmdEdit = empty($_GET['intro_cmdEdit']) ? '' : $_GET['intro_cmdEdit'];
$intro_cmdUpdate = isset($_POST['intro_cmdUpdate']);
$intro_cmdDel = empty($_GET['intro_cmdDel']) ? '' : $_GET['intro_cmdDel'];
$intro_cmdAdd = empty($_GET['intro_cmdAdd']) ? '' : $_GET['intro_cmdAdd'];
$intro_iconList = empty($_GET['intro_iconList']) ? '' : $_GET['intro_iconList'];
$intro_iconEdit = empty($_GET['intro_iconEdit']) ? '' : $_GET['intro_iconEdit'];

if (!empty ($GLOBALS['_cid'])) {
	$form = new FormValidator('introduction_text', 'post', api_get_self().'?'.api_get_cidreq());
} else {
	$form = new FormValidator('introduction_text');
}
$renderer =& $form->defaultRenderer();
$renderer->setElementTemplate('<div style="width: 80%; margin: 0px auto; padding-bottom: 10px; ">{element}</div>');

$toolbar_set = 'Introduction';
$width = '100%';
$height = '300';

// The global variable $fck_attribute has been deprecated. It stays here for supporting old external code.
global $fck_attribute;
if (is_array($fck_attribute)) {
	if (isset($fck_attribute['ToolbarSet'])) {
		$toolbar_set = $fck_attribute['ToolbarSet'];
	}
	if (isset($fck_attribute['Width'])) {
		$toolbar_set = $fck_attribute['Width'];
	}
	if (isset($fck_attribute['Height'])) {
		$toolbar_set = $fck_attribute['Height'];
	}
}

if (is_array($editor_config)) {
	if (!isset($editor_config['ToolbarSet'])) {
		$editor_config['ToolbarSet'] = $toolbar_set;
	}
	if (!isset($editor_config['Width'])) {
		$editor_config['Width'] = $width;
	}
	if (!isset($editor_config['Height'])) {
		$editor_config['Height'] = $height;
	}
} else {
	$editor_config = array('ToolbarSet' => $toolbar_set, 'Width' => $width, 'Height' => $height);
}

$form->add_html_editor('intro_content', null, null, false, $editor_config);
$form->addElement('style_submit_button', 'intro_cmdUpdate', get_lang('SaveIntroText'), 'class="save"');

/*	INTRODUCTION MICRO MODULE - COMMANDS SECTION (IF ALLOWED) */
$course_id = api_get_course_int_id();

if ($intro_editAllowed) {
	$moduleId = Database::escape_string($moduleId);
	
	/* Replace command */
	if ($intro_cmdUpdate) {
		if ($form->validate()) {
			$form_values = $form->exportValues();
			$intro_content = Security::remove_XSS(stripslashes(api_html_entity_decode($form_values['intro_content'])), COURSEMANAGERLOWSECURITY);			
			if (!empty($intro_content)) {
				$sql = "REPLACE $TBL_INTRODUCTION SET c_id = $course_id, id='$moduleId',intro_text='".Database::escape_string($intro_content)."', session_id='".intval($session_id)."'";				
				Database::query($sql);
				$introduction_section .= Display::return_message(get_lang('IntroductionTextUpdated'),'confirmation', false);
			} else {
				$intro_cmdDel = true;	// got to the delete command
			}
		} else {
			$intro_cmdEdit = true;
		}
	}

	/* Delete Command */
	if ($intro_cmdDel) {
		Database::query("DELETE FROM $TBL_INTRODUCTION WHERE c_id = $course_id AND id='".$moduleId."' AND session_id='".intval($session_id)."'");
		$introduction_section .= Display::return_message(get_lang('IntroductionTextDeleted'), 'confirmation');
	}
}


/*	INTRODUCTION MICRO MODULE - DISPLAY SECTION */

/* Retrieves the module introduction text, if exist */

$sql = "SELECT intro_text FROM $TBL_INTRODUCTION 
        WHERE c_id = $course_id AND id='".Database::escape_string($moduleId)."' AND session_id = '".intval($session_id)."'";
$intro_dbQuery = Database::query($sql);
if (Database::num_rows($intro_dbQuery) > 0) {
	$intro_dbResult = Database::fetch_array($intro_dbQuery);    
	$intro_content = $intro_dbResult['intro_text'];	
} else {
	$intro_content = '';
}

/* Determines the correct display */

if ($intro_cmdEdit || $intro_cmdAdd) {
	$intro_dispDefault = false;
	$intro_dispForm = true;
	$intro_dispCommand = false;
} else if ($intro_iconList || $intro_iconEdit) {
	$intro_dispDefault  = false;
	$intro_dispForm     = false;
	$intro_dispCommand  = false;
} else {
	$intro_dispDefault = true;
	$intro_dispForm = false;

	if ($intro_editAllowed) {
		$intro_dispCommand = true;
	} else {
		$intro_dispCommand = false;
	}
}

/* INTRODUCTION MICRO MODULE - DISPLAY ICON LIST */
if ($intro_editAllowed) {
    if ($intro_iconList) {
        $toolsList = CourseHome::tools_iconListAction();
        $introduction_section .= '<table style="width:100%;">';
        foreach ($toolsList as $tool) {
            $introduction_section .= '<tr>';
            $introduction_section .= '<td><a href="' . api_get_path(WEB_CODE_PATH) . $tool['link'] . '?' . api_get_cidreq() . '"><img src="' . api_get_path(WEB_IMG_PATH).$tool['image']. '"></a></td>';
            $introduction_section .= '<td><a href="' . api_get_path(WEB_CODE_PATH) . $tool['link'] . '?' . api_get_cidreq() . '">' .  $tool['name']. '</a></td>';
            $introduction_section .= '<td><a class="btn btn-primary" href="' . api_get_self() . '?intro_iconEdit=' . $tool['id'] . '">' . get_lang('Edit'). '</a></td>';
            $delete = (!empty($tool['custom_icon'])) ? '<a class="btn btn-danger" href="' . api_get_self() . '?intro_iconEdit=' . $tool['id'] . '">' . get_lang('Delete'). '</a>' : '';
            $introduction_section .= '<td>' . $delete . '</td>';
            $introduction_section .= '</tr>';
        }
        $introduction_section .= '</table>';

    }
}

/* INTRODUCTION MICRO MODULE - EDIT ICON */
if ($intro_editAllowed) {
    if (!empty($intro_iconEdit)) {
        //defaults
        $course_tool_table  = Database::get_course_table(TABLE_TOOL_LIST);
        $course_id = api_get_course_int_id();

        $sql = "SELECT * FROM $course_tool_table  WHERE category = 'authoring' AND id = $intro_iconEdit AND c_id = $course_id";
        $result = Database::query($sql);
        $tool = Database::fetch_assoc($result);

        $default['id']          = $tool['id'];
        $default['c_id']        = $tool['c_id'];
        $default['name']        = $tool['name'];
        $default['links']       = $tool['link'];
        $default['target']      = $tool['target'];
        $default['visibility']  = $tool['visibility'];
        $default['description'] = $tool['description'];

        if (!empty ($GLOBALS['_cid'])) {
            $form = new FormValidator('icon_edit', 'post', api_get_self().'?intro_iconEdit=' . $default['id']);
        } else {
            $form = new FormValidator('icon_edit');
        }

        $form->addElement('header', get_lang('EditIcon'));
        //$form->addElement('hidden', 'id');
        //$form->addElement('hidden', 'c_id');
        $form->addElement('text', 'name', get_lang('Name'));
        $form->addElement('text', 'links', get_lang('Links'));
        //$form->addElement('text', 'custom_icon', get_lang('CustomIcon'));
        $form->addElement('file', 'icon', get_lang('CustomIcon'));
        $form->addElement('select', 'target', get_lang('Target'), array('_self' => '_self', '_blank' => '_blank'));
        $form->addElement('select', 'visibility', get_lang('Visibility'), array(1 => get_lang('Visible'), 0 => get_lang('Invisible')));
        $form->addElement('textarea', 'description', get_lang('Description'),array ('rows' => '3', 'cols' => '40'));
        $form->addElement('style_submit_button', 'intro_cmdUpdate', get_lang('SaveIntroText'), 'class="save"');

        $form->setDefaults($default);

        $introduction_section .= '<div id="courseintro" style="width: 98%">';
        $introduction_section .= $form->return_form();
        $introduction_section .= '</div>';

        if ($form->validate()) {
            if (isset($_FILES['icon']['size']) && $_FILES['icon']['size'] !== 0) {
                //$picture_element = $form->getElement('icon');
                //$picture         = $picture_element->getValue();

                //Check if directory exists or create it if it doesn't
                $dir = api_get_path(SYS_COURSE_PATH).api_get_course_path().'/upload/course_home_icons';
                if (!is_dir($dir)) {  //if (!file_exists($path)) {
                    if (!mkdir($dir, api_get_permissions_for_new_directories())) {
                        //error message
                    }
                }

                //change filename if already exists
                /*$ext = explode('.', basename($document->path));
                if (count($ext) > 1) {
                    $ext = array_pop($ext);
                    $file_name_no_ext = substr($document->path, 0, - (strlen($ext) + 1));
                    $ext = '.'.$ext;
                } else {
                    $ext = '';
                    $file_name_no_ext = $document->path;
                }
                $new_file_name = $file_name_no_ext.'_'.$i.$ext;
                $file_exists = file_exists($path.$new_file_name);
                while ($file_exists) {
                    $i++;
                    $new_file_name = $file_name_no_ext.'_'.$i.$ext;
                    $file_exists = file_exists($path.$new_file_name);
                }*/

                //copy the image to the course upload folder
                if (!copy($_FILES['icon']['tmp_name'], $dir . '/' . $_FILES['icon']['name'])) {
                    //error message
                }

                //$id             = Database::escape_string($_POST['id']);
                //$c_id           = Database::escape_string($_POST['c_id']);
                $name           = Database::escape_string($_POST['name']);
                $link           = Database::escape_string($_POST['links']);
                $target         = Database::escape_string($_POST['target']);
                $custom_icon    = Database::escape_string($_FILES['icon']['name']);
                $visibility     = Database::escape_string($_POST['visibility']);
                $description    = Database::escape_string($_POST['description']);

                $sql    = "UPDATE $course_tool_table
                            SET name = '$name',
                                link = '$link',
                                visibility = '$visibility',
                                target = '$target',
                                custom_icon = '$custom_icon',
                                description = '$description'
                            WHERE c_id = $course_id AND id = $intro_iconEdit";

                $result = Database::query($sql);

            //Display :: display_confirmation_message(get_lang('IconSaved'));
            }
        }
    }
}

/* Executes the display */

// display thematic advance inside a postit
if ($intro_dispForm) {
	$default['intro_content'] = $intro_content;
	$form->setDefaults($default);
	$introduction_section .= '<div id="courseintro" style="width: 98%">';
	$introduction_section .= $form->return_form();
	$introduction_section .= '</div>';
}

$thematic_description_html = '';

if ($tool == TOOL_COURSE_HOMEPAGE && !isset($_GET['intro_cmdEdit'])) {

	$thematic = new Thematic();
	if (api_get_course_setting('display_info_advance_inside_homecourse') == '1') {
		$information_title = get_lang('InfoAboutLastDoneAdvance');
		$last_done_advance =  $thematic->get_last_done_thematic_advance();
		$thematic_advance_info = $thematic->get_thematic_advance_list($last_done_advance);
	} else if(api_get_course_setting('display_info_advance_inside_homecourse') == '2') {
		$information_title = get_lang('InfoAboutNextAdvanceNotDone');
		$next_advance_not_done = $thematic->get_next_thematic_advance_not_done();
		$thematic_advance_info = $thematic->get_thematic_advance_list($next_advance_not_done);
	} else if(api_get_course_setting('display_info_advance_inside_homecourse') == '3') {
		$information_title = get_lang('InfoAboutLastDoneAdvanceAndNextAdvanceNotDone');
		$last_done_advance =  $thematic->get_last_done_thematic_advance();
		$next_advance_not_done = $thematic->get_next_thematic_advance_not_done();
		$thematic_advance_info = $thematic->get_thematic_advance_list($last_done_advance);
		$thematic_advance_info2 = $thematic->get_thematic_advance_list($next_advance_not_done);
	}

	if (!empty($thematic_advance_info)) {
		
		$thematic_advance = get_lang('CourseThematicAdvance').'&nbsp;'.$thematic->get_total_average_of_thematic_advances().'%';		
		if (api_is_allowed_to_edit(null, true)) {
			//$thematic_advance = '<a href="'.api_get_path(WEB_CODE_PATH).'course_progress/index.php?action=thematic_details&'.api_get_cidreq().'">'.get_lang('CourseThematicAdvance').'&nbsp;'.$thematic->get_total_average_of_thematic_advances().'%</a>';
		}		
		$thematic_info = $thematic->get_thematic_list($thematic_advance_info['thematic_id']);
		
		$thematic_advance_info['start_date'] = api_get_local_time($thematic_advance_info['start_date']);
		$thematic_advance_info['start_date'] = api_format_date($thematic_advance_info['start_date'], DATE_TIME_FORMAT_LONG);
		
		$thematic_description_html = '<div class="thematic-postit">
								  	  <div class="thematic-postit-top"><h3><a class="thematic-postit-head" style="" href="#"> '.$thematic_advance.'</h3></a></div>						 
								  	  <div class="thematic-postit-center" style="display:none">';
		$thematic_description_html .= '<div><strong>'.$thematic_info['title'].'</strong></div>';			
		$thematic_description_html .= '<div style="font-size:8pt;"><strong>'.$thematic_advance_info['start_date'].'</strong></div>';
		$thematic_description_html .= '<div>'.$thematic_advance_info['content'].'</div>';
		$thematic_description_html .= '<div>'.get_lang('DurationInHours').' : '.$thematic_advance_info['duration'].'</div>';
		
        
		if (!empty($thematic_advance_info2)){
			$thematic_info2 = $thematic->get_thematic_list($thematic_advance_info2['thematic_id']);
		
			$thematic_advance_info2['start_date'] = api_get_local_time($thematic_advance_info2['start_date']);
			$thematic_advance_info2['start_date'] = api_format_date($thematic_advance_info2['start_date'], DATE_TIME_FORMAT_LONG);
														  								
			$thematic_description_html .= '<div><strong>'.$thematic_info2['title'].'</strong></div>';			
			$thematic_description_html .= '<div style="font-size:8pt;"><strong>'.$thematic_advance_info2['start_date'].'</strong></div>';
			$thematic_description_html .= '<div>'.$thematic_advance_info2['content'].'</div>';
			$thematic_description_html .= '<div>'.get_lang('DurationInHours').' : '.$thematic_advance_info2['duration'].'</div>';
			$thematic_description_html .= '<br />';								  	
		}								  	
		$thematic_description_html .= '</div>
								  <div class="thematic-postit-bottom"></div>
								  </div>';
	} 
}

$introduction_section .= '<div class="row"><div class="span12">';
$introduction_section .=  $thematic_description_html;
$introduction_section .=  '</div>';

$introduction_section .=  '<div class="span12">';
if ($intro_dispDefault) {
    $intro_content = $intro_content;
    if (!empty($intro_content))	{
        $introduction_section .=  $intro_content;
        $introduction_section .= '<div class="span12">';
        $introduction_section .= "<a href=\"".api_get_self()."?intro_iconList=1\">".Display::return_icon('edit.png',get_lang('Modify'),'',ICON_SIZE_SMALL)."</a>";
        $introduction_section .= '</div>';
    }
}
$introduction_section .=  '</div>';

if ($intro_dispCommand) {
    if (empty($intro_content)) {
        // Displays "Add intro" commands
        $introduction_section .=  '<div id="courseintro_empty">';
        if (!empty ($GLOBALS['_cid'])) {
            $introduction_section .=  "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;intro_cmdAdd=1\">";
            $introduction_section .=  Display::return_icon('introduction_add.gif', get_lang('AddIntro')).' ';
            $introduction_section .=  "</a>";
        } else {
            $introduction_section .= "<a href=\"".api_get_self()."?intro_cmdAdd=1\">\n".get_lang('AddIntro')."</a>";
        }
        $introduction_section .= "</div>";

    } else {
    // Displays "edit intro && delete intro" commands
        $introduction_section .=  '<div id="courseintro_empty">';
        if (!empty ($GLOBALS['_cid'])) {
            $introduction_section .=  "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;intro_cmdEdit=1\">".Display::return_icon('edit.png',get_lang('Modify'),'',ICON_SIZE_SMALL)."</a>";
            $introduction_section .=  "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;intro_cmdDel=1\" onclick=\"javascript:if(!confirm('".addslashes(api_htmlentities(get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset))."')) return false;\">".Display::return_icon('delete.png',get_lang('Delete'),'',ICON_SIZE_SMALL)."</a>";
        } else {
            $introduction_section .=  "<a href=\"".api_get_self()."?intro_cmdEdit=1\">".Display::return_icon('edit.png',get_lang('Modify'),'',ICON_SIZE_SMALL)."</a>";
            $introduction_section .=  "<a href=\"".api_get_self()."?intro_cmdDel=1\" onclick=\"javascript:if(!confirm('".addslashes(api_htmlentities(get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset))."')) return false;\">".Display::return_icon('delete.png',get_lang('Delete'),'',ICON_SIZE_SMALL)."</a>";
        }
        $introduction_section .=  "</div>";
    }
}
$introduction_section .=  '</div>';