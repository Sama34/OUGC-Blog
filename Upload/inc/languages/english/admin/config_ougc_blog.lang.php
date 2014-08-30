<?php

/***************************************************************************
 *
 *	OUGC Blog plugin (/inc/languages/english/admin/ougc_blog.lang.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Adds a blog system to your forum.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Plugin API
$l['setting_group_ougc_blog'] = 'OUGC Blog';
$l['setting_group_ougc_blog_desc'] = 'Adds a blog system to your forum.';

// Settings
$l['setting_ougc_blog_seo_scheme'] = 'Page URL Scheme';
$l['setting_ougc_blog_seo_scheme_desc'] = 'Enter the Page URL scheme. Leave empty to disable SEO URLs for blog.';
$l['setting_ougc_blog_seo_scheme_categories'] = 'Category URL Scheme';
$l['setting_ougc_blog_seo_scheme_categories_desc'] = 'Enter the Category URL scheme. Leave empty to disable SEO URLs for Categories.';
//$l['setting_ougc_blog_perpage'] = 'Items Per Page';
//$l['setting_ougc_blog_perpage_desc'] = 'Maximum number of items to show per page in the ACP list.';

// ACP
$l['ougc_blog_manage'] = 'Manage blog';
$l['ougc_blog_manage_desc'] = 'This section allows you to update your blog.';
$l['ougc_blog_tab_add'] = 'Add New Page';
$l['ougc_blog_tab_add_desc'] = 'Here you can add a new page.';
$l['ougc_blog_tab_import'] = 'Import Page';
$l['ougc_blog_tab_import_desc'] = 'Here you can import a new page.';
$l['ougc_blog_tab_edit'] = 'Edit Page';
$l['ougc_blog_tab_edit_desc'] = 'Here you can edit a page.';
$l['ougc_blog_tab_edit_cat'] = 'Edit Category';
$l['ougc_blog_tab_edit_cat_desc'] = 'Here you can edit a page category.';
$l['ougc_blog_tab_cat'] = 'Categories';
$l['ougc_blog_tab_cat_desc'] = 'This section allows you to update your page categories.';
$l['ougc_blog_tab_cat_add'] = 'Add New Category';
$l['ougc_blog_tab_cat_add_desc'] = 'Here you can add a new page category.';
$l['ougc_blog_view_empty'] = 'There are currently no blog to show.';
$l['ougc_blog_form_category'] = 'Page Category';
$l['ougc_blog_form_category_desc'] = 'Select the page category where this page goes in.';
$l['ougc_blog_form_name'] = 'Name';
$l['ougc_blog_form_name_desc'] = 'Insert the name for this category/page.';
$l['ougc_blog_form_description'] = 'Description';
$l['ougc_blog_form_description_desc'] = 'Insert the description for this category/page.';
$l['ougc_blog_form_url'] = 'Unique URL';
$l['ougc_blog_form_url_desc'] = 'Insert the unique URL identifier for this category/page.';
$l['ougc_blog_form_import'] = 'Local File';
$l['ougc_blog_form_import_desc'] = 'Select the XML page file to import from your computer.';
$l['ougc_blog_form_import_url'] = 'URL File';
$l['ougc_blog_form_import_url_desc'] = 'Insert the XML page URL to import.';
$l['ougc_blog_form_import_ignore_version'] = 'Ignore Version Compatibility';
$l['ougc_blog_form_import_ignore_version_desc'] = 'Should this page be imported regardless of the version of OUGC blog / Page Manager it was created for?';
$l['ougc_blog_form_category'] = 'Category';
$l['ougc_blog_form_disabled'] = 'Disabled';
$l['ougc_blog_form_disabled_desc'] = 'Disabled';
$l['ougc_blog_form_visible'] = 'Active';
$l['ougc_blog_form_visible_desc'] = 'Whether if this page is active or disabled.';
$l['ougc_blog_form_breadcrumb'] = 'Show in Breadcrumb';
$l['ougc_blog_form_breadcrumb_desc'] = 'Whether if to show this category in the navigation breadcrumb.';
$l['ougc_blog_form_navigation'] = 'Show Navigation';
$l['ougc_blog_form_navigation_desc'] = 'Whether if to show a previous/next pagination in this category in blog.';
$l['ougc_blog_form_php'] = 'PHP Code';
$l['ougc_blog_form_php_desc'] = 'Whether if process this page as plain PHP code or use the MyBB template system instead.';
$l['ougc_blog_form_wol'] = 'Show In Who Is On-line List';
$l['ougc_blog_form_wol_desc'] = 'Whether if show this page within the WOL list.';
$l['ougc_blog_form_wrapper'] = 'Use Template Wrapper';
$l['ougc_blog_form_wrapper_desc'] = 'Whether or not to use the template wrapper for non-PHP blog.';
$l['ougc_blog_form_init'] = 'Run At Initialization';
$l['ougc_blog_form_init_desc'] = 'Whether or not to run PHP script at initialization ("No" to run at <i>global_end</i>).';
$l['ougc_blog_form_template'] = 'Template';
$l['ougc_blog_form_template_desc'] = 'Insert the page template below.';
$l['ougc_blog_form_groups'] = 'Allowed Groups';
$l['ougc_blog_form_groups_desc'] = 'Select the groups that can view this category/page.';
$l['ougc_blog_button_disponder'] = 'Update Display Orders';
$l['ougc_blog_button_submit'] = 'Submit';
$l['ougc_blog_form_export'] = 'Export';
$l['ougc_blog_view_page'] = 'View';

// ACP Module: Messages
$l['ougc_blog_error_update'] = 'OUGC blog requires updating. Please deactivate and re-activate the plug-in to fix this issue.';
$l['ougc_blog_error_add'] = 'There was a error while creating a new category';
$l['ougc_blog_error_invalidname'] = 'The inserted name is invalid.';
$l['ougc_blog_error_invaliddescription'] = 'The inserted description is invalid.';
$l['ougc_blog_error_invalidcategory'] = 'The selected category is invalid.';
$l['ougc_blog_error_invalidurl'] = 'The inserted unique URL is invalid.';
$l['ougc_blog_error_invalidimage'] = 'The inserted image is too long.';
$l['ougc_blog_error_invalidimport'] = 'The page content seems to be invalid.';
$l['ougc_blog_error_invalidversion'] = 'The page content seems to be from an invalid plug-in version.';
$l['ougc_blog_success_add'] = 'The category was created successfully.';
$l['ougc_blog_success_edit'] = 'The category/user was edited successfully.';
$l['ougc_blog_success_delete'] = 'The category was deleted successfully.';

// Admin Permissions
$l['ougc_blog_config_permissions'] = 'Can manage blog?';

// PluginLibrary
$l['ougc_blog_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
$l['ougc_blog_pl_old'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later, whereas your current version is {3}.';