<?php
/**
* @version		$Id: timestamp2message.php 2024.10.28.
* @package		timestamp2message
* @copyright	Copyright (C) 2024 hgh-esn All rights reserved.
* @license		GNU/GPL, see license ...
*
* new in J4
*
*/
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;     // https://forum.joomla.de/thread/12094-woher-kennt-meine-eigene-php-datei-die-datei-mit-der-elternklasse/?postID=74539#post74539
use Joomla\CMS\Factory;

// jimport('joomla.plugin.plugin');

//	$art_id = '-artikel-id-';

// class plgContentTimestamp2message extends JPlugin   // old
class plgContentTimestamp2message extends CMSPlugin    // new since J4
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       4.4.9
	 */

	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
 		$this->loadLanguage();
	}

	public function onAfterDispatch()
	{
		$app = Factory::getApplication();
		
		// Retrieve messages.
		$messages = $app->getMessageQueue(true);
		$url=$_SERVER["REQUEST_URI"];		
		$cookie_name = "last_edit_id";
		// *
		// * when fallinng back to listviews we have this urls 
		// *
		if 	(		stristr($url,'option=com_categories&view=categories&extension=com_content')
				||	stristr($url,'option=com_snippets&view=items')
				||	stristr($url,'option=com_content&view=articles')
			)
		{	
			$id= $_COOKIE[$cookie_name];    // get last id from qookie
		}	
		elseif(stristr($url,'com_snippets'))
		{
			$id_start=strpos($url,'&id=')+4;   // Korrektur id beginnt 0...4 Stellen mehr recht
			$id_end =strlen($url);
			$id=substr($url,$id_start,$id_end-$id_start);

			$this->mySetQookie($id,$cookie_name);
		}
		elseif (stristr($url,'com_categories'))  // must be before com_content because it also contains that string
		{
			// echo '<div>' .'url-com_caregories=' .$url .'</div>';
			// https://j4-2-x.hgh-web.de/administrator/index.php?option=com_categories&view=category&layout=edit&id=276&extension=com_content
			$id_start=strpos($url, 'edit&id=')+8;   // Korrektur id beginnt 0...8 Stellen mehr rechts
			$id_end =strpos($url, '&extension=');
			$id=substr($url,$id_start,$id_end-$id_start);

			$this->mySetQookie($id,$cookie_name);
		}		
		elseif (stristr($url,'com_content'))
		{
			
			if (stristr($url,'&return='))
			{
				$id_start=strpos($url, '&a_id=')+6;   // Korrektur id beginnt 0...6 Stellen mehr rechts
				$id_end  =strpos($url,'&return=');
			}
			else
			{
				$id_start=strpos($url, 'edit&id=')+8;   // Korrektur id beginnt 0...6 Stellen mehr rechts
				$id_end=strlen($url);
			}
			
			$id=substr($url,$id_start,$id_end-$id_start);
			
			$this->mySetQookie($id,$cookie_name);
		}
/*
		 	echo '<div>' .'str_start=' .$id_start .'</div>';
		 	echo '<div>' .'str_end=' .$id_end .'</div>';
			echo '<div>' .'id=' .$id .'</div>';
*/
	foreach ($messages as $k => $message)
		{
			if ($message['message'] === $app->getLanguage()->_(sprintf('JLIB_APPLICATION_SAVE_SUCCESS')))   // Item saved. ... used by RL-snippets edit
			{
				$msg=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [snippet_id=' .$id .']';
	 			$app->enqueueMessage($msg, 'message');
			}
			elseif ($message['message'] === $app->getLanguage()->_(sprintf('COM_CONTENT_SAVE_SUCCESS')))    // article saved. .. used by Jx-article edit
			{
				$msg1=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [article_id=' .$id .']';
	 			$app->enqueueMessage($msg1, 'message');
			}
			elseif ($message['message'] === $app->getLanguage()->_(sprintf('COM_CATEGORIES_SAVE_SUCCESS'))) // category saved. .. used by Jx-category edit
			{
				$msg2=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [category_id=' .$id .']';
	 			$app->enqueueMessage($msg2, 'message');
			}

			// Repopulate the message queue.
			$app->enqueueMessage($message['message'], $message['type']);   // Einsteuern der Ursprungsmeldung			.... ohne Zeitangabe

		}
	}
	public function mySetQookie($id, $cookie_name)
	{
		$cookie_value = $id;
		unset($_COOKIE[$cookie_name]);
		setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
		return;		
	}
}
?>
