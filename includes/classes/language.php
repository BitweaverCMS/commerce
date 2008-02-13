<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: language.php,v 1.6 2008/02/13 01:48:42 spiderr Exp $
//

  class language {
    var $languages, $catalog_languages, $browser_languages, $language;

    function language($lng = '') {
      global $gBitDb;
      $this->languages = array('ar' => array('ar([-_][[:alpha:]]{2})?|arabic', 'arabic', 'ar'),
                               'bg-win1251' => array('bg|bulgarian', 'bulgarian-win1251', 'bg'),
                               'bg-koi8r' => array('bg|bulgarian', 'bulgarian-koi8', 'bg'),
                               'ca' => array('ca|catalan', 'catala', 'ca'),
                               'cs-iso' => array('cs|czech', 'czech-iso', 'cs'),
                               'cs-win1250' => array('cs|czech', 'czech-win1250', 'cs'),
                               'da' => array('da|danish', 'danish', 'da'),
                               'de' => array('de([-_][[:alpha:]]{2})?|german', 'german', 'de'),
                               'el' => array('el|greek',  'greek', 'el'),
                               'en' => array('en([-_][[:alpha:]]{2})?|english', 'english', 'en'),
                               'es' => array('es([-_][[:alpha:]]{2})?|spanish', 'spanish', 'es'),
                               'et' => array('et|estonian', 'estonian', 'et'),
                               'fi' => array('fi|finnish', 'finnish', 'fi'),
                               'fr' => array('fr([-_][[:alpha:]]{2})?|french', 'french', 'fr'),
                               'gl' => array('gl|galician', 'galician', 'gl'),
                               'he' => array('he|hebrew', 'hebrew', 'he'),
                               'hu' => array('hu|hungarian', 'hungarian', 'hu'),
                               'id' => array('id|indonesian', 'indonesian', 'id'),
                               'it' => array('it|italian', 'italian', 'it'),
                               'ja-euc' => array('ja|japanese', 'japanese-euc', 'ja'),
                               'ja-sjis' => array('ja|japanese', 'japanese-sjis', 'ja'),
                               'ko' => array('ko|korean', 'korean', 'ko'),
                               'ka' => array('ka|georgian', 'georgian', 'ka'),
                               'lt' => array('lt|lithuanian', 'lithuanian', 'lt'),
                               'lv' => array('lv|latvian', 'latvian', 'lv'),
                               'nl' => array('nl([-_][[:alpha:]]{2})?|dutch', 'dutch', 'nl'),
                               'no' => array('no|norwegian', 'norwegian', 'no'),
                               'pl' => array('pl|polish', 'polish', 'pl'),
                               'pt-br' => array('pt[-_]br|brazilian portuguese', 'brazilian_portuguese', 'pt-BR'),
                               'pt' => array('pt([-_][[:alpha:]]{2})?|portuguese', 'portuguese', 'pt'),
                               'ro' => array('ro|romanian', 'romanian', 'ro'),
                               'ru-koi8r' => array('ru|russian', 'russian-koi8', 'ru'),
                               'ru-win1251' => array('ru|russian', 'russian-win1251', 'ru'),
                               'sk' => array('sk|slovak', 'slovak-iso', 'sk'),
                               'sk-win1250' => array('sk|slovak', 'slovak-win1250', 'sk'),
                               'sr-win1250' => array('sr|serbian', 'serbian-win1250', 'sr'),
                               'sv' => array('sv|swedish', 'swedish', 'sv'),
                               'th' => array('th|thai', 'thai', 'th'),
                               'tr' => array('tr|turkish', 'turkish', 'tr'),
                               'uk-win1251' => array('uk|ukrainian', 'ukrainian-win1251', 'uk'),
                               'zh-tw' => array('zh[-_]tw|chinese traditional', 'chinese_big5', 'zh-TW'),
                               'zh' => array('zh|chinese simplified', 'chinese_gb', 'zh'));


      $this->catalog_languages = array();
      $languages_query = "SELECT `languages_id`, `name`, `code`, `image`, `directory`
                          FROM " . TABLE_LANGUAGES . "
                          ORDER BY `sort_order`";

      $languages = $gBitDb->query($languages_query);

      while (!$languages->EOF) {
        $this->catalog_languages[$languages->fields['code']] = array('id' => $languages->fields['languages_id'],
                                                             'name' => $languages->fields['name'],
                                                             'image' => $languages->fields['image'],
                                                             'directory' => $languages->fields['directory']);
        $languages->MoveNext();
      }
      $this->browser_languages = '';
      $this->language = '';

      $this->set_language($lng);
    }

    function set_language($language) {
      if ( (zen_not_null($language)) && (isset($this->catalog_languages[$language])) ) {
        $this->language = $this->catalog_languages[$language];
      } else {
        $this->language = $this->catalog_languages[DEFAULT_LANGUAGE];
      }
    }

	function load( $pLangCode ) {
	  global $gBitDb;
	  if( $rs = $gBitDb->query( "SELECT * FROM " . TABLE_LANGUAGES . " WHERE `code`=?", array( $pLangCode ) ) ) {
	  	$this->mInfo  = $rs->fields;
	  }
	  return( count( $this->mInfo ) );
	}

    function get_browser_language() {
		if( !empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$this->browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

			for ($i=0, $n=sizeof($this->browser_languages); $i<$n; $i++) {
				reset($this->languages);
				while (list($key, $value) = each($this->languages)) {
					if (eregi('^(' . $value[0] . ')(;q=[0-9]\\.[0-9])?$', $this->browser_languages[$i]) && isset($this->catalog_languages[$key])) {
						$this->language = $this->catalog_languages[$key];
						break 2;
					}
				}
			}
		}
    }
  }
?>
