<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language file definitions for Panopto repository plugin.
 *
 * @package    repository_panopto
 * @copyright  2017 Lancaster University (http://www.lancaster.ac.uk/)
 * @author     Ruslan Kabalin (https://github.com/kabalin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['applicationkey'] = 'Identitiy Provider Application Key';
$string['applicationkeydesc'] = 'Application Key from Panopto Identity Providers settings, e.g. \'00000000-0000-0000-0000-000000000000\'.';
$string['bouncepageurl'] = 'Bounce Page URL';
$string['bouncepageurldesc'] = 'In the Panopto Identity Providers settings, set Bounce Page URL to {$a} in order to enable SSO.';
$string['bouncepageurlnotreadydesc'] = 'Visit this page after saving plugin configuration to look up Bounce Page URL you need to use in Panopto Identity Providers settings.';
$string['cachedef_folderstree'] = 'Panopto folders tree for the user.';
$string['configplugin'] = 'Panopto configuration';
$string['connectionsettings'] = 'Connection settings';
$string['created'] = 'Created';
$string['duration'] = 'Duration';
$string['errorsessionnotfound'] = 'This session is not found on Panopto. It might have been deleted.';
$string['errornosessionaccess'] = 'You do not have access rights to this session, this activity might have been added by the different staff member who has access to this video. You still can choose a different video and save, but you won\'t be able to revert to this one after that.';
$string['folderstreecachettl'] = 'Folders tree cache TTL';
$string['folderstreecachettldesc'] = 'Set duration in seconds when folders tree cache will be valid (300 seconds by default). This speeds up folders navigation in repository interface, but changes made remotely on Panopto (e.g. new folder created) will be reflected in the interface when local cache has expired. Setting to 0 will disable folders tree cache.';
$string['instancename'] = 'Identitiy Provider Instance Name';
$string['instancenamedesc'] = 'Instance name from Panopto Identity Providers settings.';
$string['viewerurl'] = 'URL';
$string['panopto:view'] = 'View Panopto repository';
$string['password'] = 'Panopto API user password';
$string['passworddesc'] = 'Password for API user authentication.';
$string['pluginname'] = 'Panopto';
$string['pluginnotice'] = 'Notice, this repository plugin is designed to work with Panopto Activity module only. Please make sure you have mod_panopto installed. This repositiry can\'t be used outside mod_panopto at the moment.';
$string['serverhostname'] = 'Panopto server hostname';
$string['serverhostnamedesc'] = 'FQDN of your Panopto server, e.g. \'demo.hosted.panopto.com\'.';
$string['title'] = 'Title';
$string['userkey'] = 'Panopto API username';
$string['userkeydesc'] = 'User on the Panopto server to use for API calls, it needs to have Administrator rights.';
$string['viewonpanopto'] = 'View this video on Panopto';
