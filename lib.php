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
 * Panopto repository plugin.
 *
 * @package    repository_panopto
 * @copyright  2017 Lancaster University (http://www.lancaster.ac.uk/)
 * @author     Ruslan Kabalin (https://github.com/kabalin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/local/panopto/lib/panopto/lib/Client.php");
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Repository plugin for accessing Panopto files.
 *
 * @package    repository_panopto
 * @copyright  2017 Lancaster University (http://www.lancaster.ac.uk/)
 * @author     Ruslan Kabalin (https://github.com/kabalin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_panopto extends repository {
    /** Current client version in use. */
    const ROOT_FOLDER_ID = '00000000-0000-0000-0000-000000000000';

    /** @var stdClass Session Management client */
    private $smclient;

    /** @var stdClass User Management client */
    private $umclient;

    /** @var stdClass AuthenticationInfo object */
    private $auth;

    /**
     * Constructor
     *
     * @param int $repositoryid repository instance id.
     * @param int|stdClass $context a context id or context object.
     * @param array $options repository options.
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        global $USER;
        parent::__construct($repositoryid, $context, $options);

        // Instantiate Panopto client.
        $panoptoclient = new \Panopto\Client(get_config('panopto', 'serverhostname'), array('keep_alive' => 0));
        $panoptoclient->setAuthenticationInfo(
                get_config('panopto', 'instancename') . '\\' . $USER->username, '', get_config('panopto', 'applicationkey'));
        $this->auth = $panoptoclient->getAuthenticationInfo();
        $this->smclient = $panoptoclient->SessionManagement();
    }

    /**
     * Given a path, and perhaps a search, get a list of files.
     *
     * @param string $path identifier for current path.
     * @param string $page the page number of file list.
     * @return array list of files including meta information as specified by parent.
     */
    public function get_listing($path = '', $page = '') {
        // Data preparation.
        if (empty($path)) {
            $path = self::ROOT_FOLDER_ID;
        }
        $navpath = array();

        // Split the path requested.
        $patharray = explode('/', $path);
        // Build navigation path.
        // TODO: Change the code to build path recursevly.
        $navpathitem = '';
        foreach ($patharray as $pathitem) {
            if ($pathitem === self::ROOT_FOLDER_ID) {
                // Root dir.
                $navpathitem = $pathitem;
                $navpath[] = array('name' => get_string('pluginname', 'repository_panopto'), 'path' => $navpathitem);
            } else {
                // Getting deeper in subdirs...
                // Determine folder name first.
                $param = new \Panopto\SessionManagement\GetFoldersById($this->auth, array($pathitem));
                $folders = $this->smclient->GetFoldersById($param)->getGetFoldersByIdResult();
                // Add navigation path item.
                $navpathitem = $navpathitem . '/' . $pathitem;
                $navpath[] = array('name' => $folders[0]->getName(), 'path' => $navpathitem);

            }
        }

        // Get the folders and sessions list for the current path.
        $listfolders = $this->get_folders_list($path);
        $listfiles = $this->get_sessions_list($path);
        $list = array_merge($listfolders, $listfiles);

        // Output result.
        $listing = $this->get_base_listing();
        $listing['list'] = $list;
        $listing['path'] = $navpath;
        return $listing;
    }

    /**
     * Search for results
     * @param   string  $key    The search string
     * @param   int     $page   The search page
     * @return  array   A set of results with the same layout as the 'list' element in 'get_listing'.
     */
    public function search($key, $page = 0) {

        // Get the folders and sessions list for the current path.
        $listfolders = $this->get_folders_list(self::ROOT_FOLDER_ID, $key);
        $listfiles = $this->get_sessions_list(self::ROOT_FOLDER_ID, $key);
        $list = array_merge($listfolders, $listfiles);

        // Output result.
        $listing = $this->get_base_listing();
        $listing['issearchresult'] = true;
        $listing['list'] = $list;
        return $listing;
    }

    /**
     * Given a path, get a list of Panopto directories.
     *
     * @param string $path identifier for current path.
     * @param string $search the search query.
     * @return array list of folders with the same layout as the 'list' element in 'get_listing'.
     */
    private function get_folders_list($path, $search = '') {
        global $OUTPUT;
        $list = array();

        // Build the GetFoldersList request and perform the call.
        $pagination = new \Panopto\RemoteRecorderManagement\Pagination();
        $pagination->setPageNumber(0);
        $pagination->setMaxNumberResults(1000);

        $request = new \Panopto\SessionManagement\ListFoldersRequest();
        $request->setPagination($pagination);
        $request->setSortBy('Name');
        $request->setSortIncreasing(true);

        // If we are searching, there is no need to set parent folder,
        // also a good idea to search by relevance.
        if (!empty($search)) {
            $request->setWildcardSearchNameOnly(true);
        } else {
            // Split the path requested.
            $patharray = explode('/', $path);
            // Determine the curent directory to show.
            $currentfolderid = end($patharray);
            $request->setParentFolderId($currentfolderid);
        }

        $param = new \Panopto\SessionManagement\GetFoldersList($this->auth, $request, $search);
        $folders = $this->smclient->GetFoldersList($param)->getGetFoldersListResult();
        $totalfolders = $folders->getTotalNumberResults();

        // Processing GetFoldersList result.
        if ($totalfolders) {
            foreach ($folders->getResults() as $folder) {
                $list[] = array(
                    'title' => $folder->getName(),
                    'path' => $path . '/' . $folder->getId(),
                    'thumbnail' => $OUTPUT->pix_url('f/folder-32')->out(false),
                    'children' => array(),
                );
            }
        }
        return $list;
    }

    /**
     * Given a path, get a list of Panopto sessions available for viewing.
     *
     * @param string $path identifier for current path.
     * @param string $search the search query.
     * @return array list of files with the same layout as the 'list' element in 'get_listing'.
     */
    private function get_sessions_list($path, $search = '') {
        global $OUTPUT;
        $list = array();

        // Build the GetFoldersList request and perform the call.
        $pagination = new \Panopto\RemoteRecorderManagement\Pagination();
        $pagination->setPageNumber(0);
        $pagination->setMaxNumberResults(1000);

        $request = new \Panopto\SessionManagement\ListSessionsRequest();
        $request->setPagination($pagination);
        $request->setSortBy('Name');
        $request->setSortIncreasing(true);
        $request->setStates(array('Complete'));

        // If we are not searching, set parent folder.
        if (empty($search)) {
            // Split the path requested.
            $patharray = explode('/', $path);
            // Determine the curent directory to show.
            $currentfolderid = end($patharray);
            $request->setFolderId($currentfolderid);
        }

        $param = new \Panopto\SessionManagement\GetSessionsList($this->auth, $request, $search);
        $sessions = $this->smclient->GetSessionsList($param)->getGetSessionsListResult();
        $totalsessions = $sessions->getTotalNumberResults();

        // Processing GetFoldersList result.
        if ($totalsessions) {
            foreach ($sessions->getResults() as $session) {
                $title = $session->getName();
                $url = new moodle_url($session->getViewerUrl());
                $thumburl = new moodle_url('https://' . get_config('panopto', 'serverhostname') . $session->getThumbUrl());
                $list[] = array(
                    'shorttitle' => $title,
                    'title' => $title.'.mp4', // Hack to accept this file by extension.
                    'source' => $session->getMP4Url(),
                    'url' => $url->out(false),
                    'thumbnail' => $thumburl->out(false),
                    'thumbnail_title' => $session->getDescription(),
                    'date' => $session->getStartTime()->format('U'),
                );
            }
        }
        return $list;
    }

    /**
     * Return array of default listing properties.
     *
     * @return array of listing properties.
     */
    private function get_base_listing() {
        return array(
            'dynload' => true,
            'nologin' => true,
            'path' => array(array('name' => get_string('pluginname', 'repository_panopto'), 'path' => self::ROOT_FOLDER_ID)),
            'list' => array(),
        );
    }

    /**
     * Return names of the options to display in the repository plugin config form.
     *
     * @return array of option names.
     */
    public static function get_type_option_names() {
        return array('serverhostname', 'userkey', 'password', 'instancename', 'applicationkey', 'pluginname');
    }

    /**
     * Setup repistory form.
     *
     * @param moodleform $mform Moodle form (passed by reference).
     * @param string $classname repository class name.
     */
    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);
        $strrequired = get_string('required');

        // Server hostname.
        $mform->addElement('text', 'serverhostname', get_string('serverhostname', 'repository_panopto'));
        $mform->addRule('serverhostname', $strrequired, 'required', null, 'client');
        $mform->setType('serverhostname', PARAM_HOST);
        $mform->addElement('static', 'serverhostnamedesc', '', get_string('serverhostnamedesc', 'repository_panopto'));

        // User key.
        $mform->addElement('text', 'userkey', get_string('userkey', 'repository_panopto'));
        $mform->addRule('userkey', $strrequired, 'required', null, 'client');
        $mform->setType('userkey', PARAM_RAW_TRIMMED);
        $mform->addElement('static', 'userkeydesc', '', get_string('userkeydesc', 'repository_panopto'));

        // Password.
        $mform->addElement('text', 'password', get_string('password', 'repository_panopto'));
        $mform->addRule('password', $strrequired, 'required', null, 'client');
        $mform->setType('password', PARAM_RAW_TRIMMED);
        $mform->addElement('static', 'passworddesc', '', get_string('passworddesc', 'repository_panopto'));

        // Instance name.
        $mform->addElement('text', 'instancename', get_string('instancename', 'repository_panopto'));
        $mform->addRule('instancename', $strrequired, 'required', null, 'client');
        $mform->setType('instancename', PARAM_RAW_TRIMMED);
        $mform->addElement('static', 'instancenamedesc', '', get_string('instancenamedesc', 'repository_panopto'));

        // Application key.
        $mform->addElement('text', 'applicationkey', get_string('applicationkey', 'repository_panopto'));
        $mform->addRule('applicationkey', $strrequired, 'required', null, 'client');
        $mform->setType('applicationkey', PARAM_RAW_TRIMMED);
        $mform->addElement('static', 'applicationkeydesc', '', get_string('applicationkeydesc', 'repository_panopto'));
    }

    /**
     * This repository doesn't support global search.
     *
     * @return bool if supports global search
     */
    public function global_search() {
        return false;
    }

    /**
     * This repository supports only mp4.
     *
     * In fact Panopto sessions do not have file extensions,
     * we make them mp4 in get_listing by default to make this repo work.
     *
     * @return string '*' means this repository support any files
     */
    public function supported_filetypes() {
        return 'video/mp4';
    }

    /**
     * This repository only supports external files
     *
     * @return int return type bitmask supported
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    /**
     * We do not treat Panopto site data as private.
     *
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }
}
