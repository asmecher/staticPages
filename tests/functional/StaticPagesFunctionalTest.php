<?php

/**
 * @file tests/functional/StaticPagesFunctionalTest.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPagesFunctionalTest
 * @package plugins.generic.staticPages
 *
 * @brief Functional tests for the static pages plugin.
 */

import('lib.pkp.tests.WebTestCase');

class StaticPagesFunctionalTest extends WebTestCase {
	/**
	 * Enable the plugin
	 */
	function testStaticPages() {
		$this->open(self::$baseUrl);

		parent::logIn('admin', 'admin');

		/** DUPED FROM 20-CreateJournalTest.php -- RECONCILE ME */
		$this->open(self::$baseUrl);
		$this->waitForElementPresent('link=Administration');
		$this->click('link=Administration');
		$this->waitForElementPresent('link=Hosted Journals');
		$this->click('link=Hosted Journals');
		$this->waitForElementPresent('css=[id^=component-grid-admin-journal-journalgrid-createContext-button-]');
		$this->click('css=[id^=component-grid-admin-journal-journalgrid-createContext-button-]');

		// Enter journal data
		$this->waitForElementPresent('css=[id^=name-en_US-]');
		$this->type('css=[id^=name-en_US-]', 'Journal of Public Knowledge');
		$this->type('css=[id^=name-fr_CA-]', 'Journal de la connaissance du public');
		$this->typeTinyMCE('description-en_US', 'The Journal of Public Knowledge is a peer-reviewed quarterly publication on the subject of public access to science.');
		$this->typeTinyMCE('description-fr_CA', 'Le Journal de Public Knowledge est une publication trimestrielle évaluée par les pairs sur le thème de l\'accès du public à la science.');
		$this->type('css=[id^=path-]', 'publicknowledge');
		$this->clickAndWait('css=[id^=submitFormButton-]');
		$this->waitForElementPresent('css=div.header:contains(\'Settings Wizard\')');
		$this->waitJQuery();
		/** END DUPED CODE */

		$this->waitForElementPresent($selector='link=Website');
		$this->clickAndWait($selector);
		$this->click('link=Plugins');

		// Find and enable the plugin
		$this->waitForElementPresent($selector = '//input[starts-with(@id, \'select-cell-staticpagesplugin-enabled\')]');
		$this->assertElementNotPresent('link=Static Pages'); // Plugin should be disabled
		$this->click($selector); // Enable plugin
		$this->waitForElementPresent('//div[contains(.,\'The plugin "Static Pages Plugin" has been enabled.\')]');

		// Check for a 404 on the page we are about to create
		$this->open(self::$baseUrl . '/index.php/publicknowledge/flarm');
		$this->assertText('css=h1', '404 Not Found');

		// Find the plugin's tab
		$this->open(self::$baseUrl);
		$this->waitForElementPresent($selector='css=li.profile a:contains(\'Dashboard\')');
		$this->clickAndWait($selector);
		$this->waitForElementPresent($selector='link=Website');
		$this->clickAndWait($selector);
		$this->waitForElementPresent($selector = 'link=Static Pages');
		$this->click($selector);

		// Create a static page
		$this->waitForElementPresent($selector = '//a[starts-with(@id, \'component-plugins-generic-staticpages-controllers-grid-staticpagegrid-addStaticPage-button-\')]');
		$this->click($selector);
		$this->waitForElementPresent($selector='//form[@id=\'staticPageForm\']//input[starts-with(@id, \'path-\')]');
		$this->type($selector, 'flarm');
		$this->type($selector='//form[@id=\'staticPageForm\']//input[starts-with(@id, \'title-\')]', 'Test Static Page');
		$this->typeTinyMCE('content', 'Here is my new static page.');
		$this->waitForElementPresent($selector = '//form[@id=\'staticPageForm\']//button[starts-with(@id, \'submitFormButton-\')]');
		$this->click($selector);
		$this->waitForElementNotPresent('css=div.pkp_modal_panel');

		// View the static page
		$this->waitForElementPresent($selector='//a[text()=\'flarm\']');
		$this->click($selector);
		$this->waitForPopUp('staticPage', 10000);
		$this->selectWindow('name=staticPage');
		$this->waitForElementPresent('//h2[contains(text(),\'Test Static Page\')]');
		$this->waitForElementPresent('//p[contains(text(),\'Here is my new static page.\')]');
		$this->close();
		$this->selectWindow(null);

		$this->logOut();
	}
}

