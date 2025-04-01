<?php
/**
* @version		$Id: timestamp2message.php 2024.10.28.
* @package		timestamp2message
* @copyright	Copyright (C) 2024 hgh-esn All rights reserved.
* @license		GNU/GPL, see license ...
*
* since J4
* HGH   2501     new: class .... extends CMSPlugin  for .... extends JPlugin
* HGH   250330   +    Delete qookie
* HGH   250401   +    ID-Type(snippet, category, article) now in id-variable
* HGH   250401   +    Edit Menus: com_menus now also checked 
*
*/
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;     // https://forum.joomla.de/thread/12094-woher-kennt-meine-eigene-php-datei-die-datei-mit-der-elternklasse/?postID=74539#post74539
use Joomla\CMS\Factory;

// jimport('joomla.plugin.plugin');

	$art_id = '-artikel-id-';

// class plgContentTimestamp2message extends JPlugin   // alt
class plgContentTimestamp2message extends CMSPlugin    // neu seit J4
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
/*
  function onContentPrepare($context, $article, $params, $page)
    {
		global $art_id;
		echo 'onContentPrepare';
		echo 'art_id 1=' .$art_id;
		$art_id = $article->id;
		echo 'art_id 2=' .$art_id;
	}
*/
	public function onAfterDispatch()
	{
		$app = Factory::getApplication();
		
		// Retrieve messages.
		$messages = $app->getMessageQueue(true);
		$url=$_SERVER["REQUEST_URI"];		
		$cookie_name = "last_edit_id";
		
		// *
		// * when falling back to listviews we have this urls 
		// *
		if 	(		stristr($url,'option=com_categories&view=categories&extension=com_content')
				||	stristr($url,'option=com_snippets&view=items')						// direkt edit via RL "Snippets"
				||	stristr($url,'option=com_content&view=articles')
	//			||  stristr($url,'.html')
	//			||  strpos ($url,'option=com_') == 0	// direkt edit via "RL Articles Anywhere"
	//			||  (strpos($url,'option=com_') == 0  &&  strpos($url,'.html' == 0))	// direkt edit via "RL Articles Anywhere"
			)
		{
	//	    echo 'falling back to listviews';
			if (isset( $_COOKIE[$cookie_name]))
				$id= $_COOKIE[$cookie_name];    // get last id from qookie
			
	//		setcookie($cookie_name, '', time()-3600, '/');   //  delete cookie
		}
		elseif(stristr($url,'com_menus'))
		{
			$id_start=strpos($url,'&id=')+4;   // Korrektur id beginnt 0...4 Stellen mehr recht
			$id_end=strlen($url);
			$id='menue_id=' .substr($url,$id_start,$id_end-$id_start);

			$this->mySetQookie($id,$cookie_name);
		}
		elseif(stristr($url,'com_snippets'))
		{
			$id_start=strpos($url,'&id=')+4;   // Korrektur id beginnt 0...4 Stellen mehr recht
	//		$id_end=strlen($url);
			$id_end=strpos($url,'&return=');
			$id='snippet_id=' .substr($url,$id_start,$id_end-$id_start);

			$this->mySetQookie($id,$cookie_name);
		}	
		elseif (stristr($url,'com_categories'))  // must be before com_content because it also contains that string
		{
			// echo '<div>' .'url-com_caregories=' .$url .'</div>';
			// https://j4-2-x.hgh-web.de/administrator/index.php?option=com_categories&view=category&layout=edit&id=276&extension=com_content
			$id_start=strpos($url, 'edit&id=')+8;   // Korrektur id beginnt 0...8 Stellen mehr rechts
			$id_end =strpos($url, '&extension=');
			$id='category_id=' .substr($url,$id_start,$id_end-$id_start);

			$this->mySetQookie($id,$cookie_name);
		}		
		elseif (stristr($url,'com_content'))    // article
		{
			if (stristr($url,'&return='))
			{
//			if (stristr($url,'&return='))
	//			https://j4-2-x.hgh-web.de/index.php/digital/decoder/fuer-lokomotiven/maerklin/digital/c80-6080/3611-decoder-klasse-c-k-w-st-e.html
	
				$id_start=strpos($url,'edit&id=')+8;   // Korrektur id beginnt 0...8 Stellen mehr rechts
				$id_end  =strpos($url,'&return=');
			}
			else
			{
				$id_start=strpos($url,'edit&id=')+8;   // Korrektur id beginnt 0...8 Stellen mehr rechts
				$id_end=strlen($url);
			}			
			$id='article_id=' .substr($url,$id_start,$id_end-$id_start);
			
			$this->mySetQookie($id,$cookie_name);
		}
		elseif (stristr($url,'view=form'))		
		{
			//	echo 'pos-form=' .strpos($url,'view=form');
	//			https://j4-2-x.hgh-web.de/index.php/digital/decoder.html?view=form&layout=edit&a_id=2980&catid=105&return=aHR0cHM6Ly9qNC0yLXguaGdoLXdlYi5kZS9pbmRleC5waHAvZGlnaXRhbC9kZWNvZGVyL2Z1ZXItbG9rb21vdGl2ZW4vbWFlcmtsaW4vZGlnaXRhbC9jODAtNjA4MC8zNjExLWRlY29kZXIta2xhc3NlLWMtay13LXN0LWUuaHRtbA==
				
				$id_start=strpos($url,'edit&a_id=')+10;   // Korrektur id beginnt 0...10 Stellen mehr rechts
				if (stristr($url,'catid'))
					$id_end=strpos($url,'&catid');				
				else			
					$id_end=strpos($url,'&return=');
				
				$id='article_id=' .substr($url,$id_start,$id_end-$id_start);
			
				$this->mySetQookie($id,$cookie_name);
		}
		elseif (stristr($url,'&catid'))		
		{
	//		echo '<div>pos-catid=' .strpos($url,'&catid=' .'</div>');
	//			echo 'strpos[&catid=' .strpos($url,'&catid');
	//			https://j4-2-x.hgh-web.de/index.php/digital/decoder.html?view=form&layout=edit&a_id=2980&catid=105&return=aHR0cHM6Ly9qNC0yLXguaGdoLXdlYi5kZS9pbmRleC5waHAvZGlnaXRhbC9kZWNvZGVyL2Z1ZXItbG9rb21vdGl2ZW4vbWFlcmtsaW4vZGlnaXRhbC9jODAtNjA4MC8zNjExLWRlY29kZXIta2xhc3NlLWMtay13LXN0LWUuaHRtbA==
				
				$id_start=strpos($url,'edit&a_id=')+10;   // Korrektur id beginnt 0...10 Stellen mehr rechts
				$id_end  =strpos($url,'&catid=');
				
				$id='article_id=' .substr($url,$id_start,$id_end-$id_start);
			
				$this->mySetQookie($id,$cookie_name);
		}
		elseif 	(stristr($url,'.html'))
		{
			if (stristr($url,'view=form'))		
			{
				//	echo 'pos-form=' .strpos($url,'view=form');
		//			https://j4-2-x.hgh-web.de/index.php/digital/decoder.html?view=form&layout=edit&a_id=2980&catid=105&return=aHR0cHM6Ly9qNC0yLXguaGdoLXdlYi5kZS9pbmRleC5waHAvZGlnaXRhbC9kZWNvZGVyL2Z1ZXItbG9rb21vdGl2ZW4vbWFlcmtsaW4vZGlnaXRhbC9jODAtNjA4MC8zNjExLWRlY29kZXIta2xhc3NlLWMtay13LXN0LWUuaHRtbA==
					
				$id_start=strpos($url,'edit&a_id=')+10;   // Korrektur id beginnt 0...10 Stellen mehr rechts
				if (stristr($url,'catid'))
					$id_end=strpos($url,'&catid');				
				else			
					$id_end=strpos($url,'&return=');
					
				$id='article_id=' .substr($url,$id_start,$id_end-$id_start);
				
				$this->mySetQookie($id,$cookie_name);
			}
			else
				$id= $_COOKIE[$cookie_name];    // get last id from qookie
			
	//		setcookie($cookie_name, '', time()-3600, '/');   //  delete cookie
		}
		else
		{
	// 	   	wenn der url keinen der angegebenen vorlaufenden string-anteile enthält.
	//		kommt beim editieren eines reinen articles vor.
			$id= $_COOKIE[$cookie_name];    // get last id from qookie	
		}
/*
		 	echo '<div>' .'str_start=' .$id_start .'</div>';
		 	echo '<div>' .'str_end=' .$id_end .'</div>';
			echo '<div>' .'id=' .$id .'</div>';
*/
		foreach ($messages as $k => $message)
		{
	//		$menu =& Jsite::getMenu(); 
	//		echo $menu->getActive()->title;

			if ($message['message'] === $app->getLanguage()->_(sprintf('JLIB_APPLICATION_SAVE_SUCCESS')))   // Item saved. ... used by RL-snippets edit
			{
	//			$msg=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [snippet_id=' .$id .']';
				$msg=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [' .$id .']';
	 			$app->enqueueMessage($msg, 'message');
			}
			elseif ($message['message'] === $app->getLanguage()->_(sprintf('COM_CONTENT_SAVE_SUCCESS')))    // article saved. .. used by Jx-article edit
			{
	//			$msg1=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [article_id=' .$id .']';
				$msg1=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [' .$id .']';
	 			$app->enqueueMessage($msg1, 'message');
			}
			elseif ($message['message'] === $app->getLanguage()->_(sprintf('COM_MENUS_MENU_SAVE_SUCCESS')))    // menue saved. .. used by Jx-menues edit
			{
	//			$msg1=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [menue_id=' .$id .']';
				$msg1=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [' .$id .']';
	 			$app->enqueueMessage($msg1, 'message');
			}
			elseif ($message['message'] === $app->getLanguage()->_(sprintf('COM_CATEGORIES_SAVE_SUCCESS'))) // category saved. .. used by Jx-category edit
			{
	//			$msg2=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [category_id=' .$id .']';
				$msg2=$message['message'] .'.. on ' .date(DATE_RFC822) .' / [' .$id .']';
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