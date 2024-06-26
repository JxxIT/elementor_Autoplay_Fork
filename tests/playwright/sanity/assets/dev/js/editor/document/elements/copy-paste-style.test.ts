import { test, expect } from '@playwright/test';
import WpAdminPage from '../../../../../../../pages/wp-admin-page';

test( 'A page can be saved successfully after copy-paste style', async ( { page }, testInfo ) => {
	// Arrange.
	const wpAdmin = new WpAdminPage( page, testInfo );
	const editor = await wpAdmin.openNewPage();

	await editor.closeNavigatorIfOpen();

	const heading1 = await editor.addWidget( 'heading' );
	const heading2 = await editor.addWidget( 'heading' );

	await editor.selectElement( heading1 );
	await editor.openPanelTab( 'style' );
	await editor.setColorControlValue( 'title_color', '#77A5BD' );

	// Act.
	await editor.copyElement( heading1 );

	await editor.pasteStyleElement( heading2 );

	const heading2Title = editor.getPreviewFrame().locator( '.elementor-element-' + heading2 + ' .elementor-heading-title' );

	// Assert.
	await expect( heading2Title ).toHaveCSS( 'color', 'rgb(119, 165, 189)' );

	const publishButton = page.locator( '#elementor-panel-saver-button-publish' );

	// Check that the panel footer save button is enabled.
	await expect( publishButton ).not.toHaveClass( /(^|\s)elementor-disabled(\s|$)/ );

	// Act.
	await publishButton.click();

	// Assert.
	await expect( publishButton ).toHaveClass( /(^|\s)elementor-disabled(\s|$)/, {
		timeout: 10000,
	} );
} );
