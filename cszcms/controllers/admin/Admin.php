<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * CSZ CMS
 *
 * An open source content management system
 *
 * Copyright (c) 2016, Astian Foundation.
 *
 * Astian Develop Public License (ADPL)
 * 
 * This Source Code Form is subject to the terms of the Astian Develop Public
 * License, v. 1.0. If a copy of the APL was not distributed with this
 * file, You can obtain one at http://astian.org/about-ADPL
 * 
 * @author	CSKAZA
 * @copyright   Copyright (c) 2016, Astian Foundation.
 * @license	http://astian.org/about-ADPL	ADPL License
 * @link	https://www.cszcms.com
 * @since	Version 1.0.0
 */
class Admin extends CI_Controller {

    function __construct() {
        parent::__construct();      
        define('LANG', $this->Csz_admin_model->getLang());
        $this->lang->load('admin', LANG);
        $this->template->set_template('admin');
        $this->_init();
    }

    public function _init() {
        $row = $this->Csz_admin_model->load_config();
        $pageURL = $this->Csz_admin_model->getCurPages();
        $this->template->set('core_css', $this->Csz_admin_model->coreCss());
        $this->template->set('core_js', $this->Csz_admin_model->coreJs());
        $this->template->set('title', 'Backend System | ' . $row->site_name);
        $this->template->set('meta_tags', $this->Csz_admin_model->coreMetatags('Backend System for CSZ Content Management'));
        $this->template->set('cur_page', $pageURL);
    }

    public function index() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        $this->csz_referrer->setIndex();
        $this->template->setSub('email_logs', $this->Csz_model->getValueArray('*', 'email_logs', "ip_address != ''", '', 10, 'timestamp_create', 'desc'));
        $this->template->setSub('link_stats', $this->Csz_model->getValueArray('*', 'link_statistic', "ip_address != ''", '', 20, 'timestamp_create', 'desc'));
        $this->template->setSub('total_emaillogs', $this->Csz_model->countData('email_logs'));
        $this->template->setSub('total_linkstats', $this->Csz_model->countData('link_statistic'));
        $this->template->setSub('total_member', $this->Csz_model->countData('user_admin',"user_type = 'member'"));
        $this->template->setSub('visitor_admin', $this->Csz_admin_model->chkVisitorUser($this->session->userdata('user_admin_id')));
        $this->load->library('RSSParser');
        $this->rssparser->set_feed_url($this->config->item('csz_backend_feed_url'));  /* get feed from CSZ CMS Article */
        $this->rssparser->set_cache_life(30);                       /* Set cache life time in minutes */
        $this->template->setSub('rss', $this->rssparser->getFeed(6));
        $this->template->loadSub('admin/home');
    }
    
    public function analytics() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        $settings = $this->Csz_admin_model->load_config();
        $this->csz_referrer->setIndex();
        $this->template->setJS('<!-- Step 2: Load the library. -->
        <script>
        (function(w,d,s,g,js,fjs){
          g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(cb){this.q.push(cb)}};
          js=d.createElement(s);fjs=d.getElementsByTagName(s)[0];
          js.src="https://apis.google.com/js/platform.js";
          fjs.parentNode.insertBefore(js,fjs);js.onload=function(){g.load("analytics")};
        }(window,document,"script"));
        </script>
        <script>
        gapi.analytics.ready(function() {
          // Step 3: Authorize the user.
          var CLIENT_ID = "'.$settings->ga_client_id.'";
          var VIEW_ID = "ga:'.str_replace('ga:', '', $settings->ga_view_id).'";
          gapi.analytics.auth.authorize({
            container: "auth-button",
            clientid: CLIENT_ID,
          });
          // Step 4: Create the view selector.
          var viewSelector = new gapi.analytics.ViewSelector({
            container: "view-selector"
          });
          // Step 5: Create the timeline chart. activeUsers
          var referdata = new gapi.analytics.googleCharts.DataChart({
            reportType: "ga",
            query: {
              "ids": VIEW_ID,
              "dimensions": "ga:fullReferrer",
              "metrics": "ga:sessions",
              "start-date": "30daysAgo",
              "end-date": "today",
              "sort": "-ga:sessions",
            },
            chart: {
              type: "TABLE",
              container: "timeline-refer"
            }
          });
          var timeline = new gapi.analytics.googleCharts.DataChart({
            reportType: "ga",
            query: {
              "ids": VIEW_ID,
              "dimensions": "ga:country",
              "metrics": "ga:sessions",
              "start-date": "30daysAgo",
              "end-date": "today",
            },
            chart: {
              type: "GEO",
              container: "timeline-map",
              keepAspectRatio: true,
              width: 100 + "%",
            }
          });
          var timeline2 = new gapi.analytics.googleCharts.DataChart({
            reportType: "ga",
            query: {
              "ids": VIEW_ID,
              "dimensions": "ga:date",
              "metrics": "ga:sessions",
              "start-date": "30daysAgo",
              "end-date": "today",
            },
            chart: {
              type: "LINE",
              container: "timeline-sessions",
            }
          });
          var timeline3 = new gapi.analytics.googleCharts.DataChart({
            reportType: "ga",
            query: {
              "ids": VIEW_ID,
              "dimensions": "ga:browser",
              "metrics": "ga:sessions",
              "start-date": "30daysAgo",
              "end-date": "today",
            },
            chart: {
              type: "PIE",
              container: "timeline-device",
            }
          });
          var timeline4 = new gapi.analytics.googleCharts.DataChart({
            reportType: "ga",
            query: {
              "ids": VIEW_ID,
              "dimensions": "ga:source",
              "metrics": "ga:sessions",
              "start-date": "30daysAgo",
              "end-date": "today",
            },
            chart: {
              type: "PIE",
              container: "timeline-source",
            }
          });
          // Step 6: Hook up the components to work together.
          gapi.analytics.auth.on("success", function(response) {
            timeline.set().execute();
            timeline2.set().execute();
            timeline3.set().execute();
            referdata.set().execute();
            timeline4.set().execute();
          });
        });
        </script>');
        $this->template->setSub('settings', $settings);
        $this->template->loadSub('admin/analytics');
    }
    
    public function deleteEmailLogs() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        admin_helper::is_not_admin($this->session->userdata('admin_type'));
        admin_helper::chkVisitor($this->session->userdata('user_admin_id'));
        //Delete the languages
        if($this->uri->segment(4)) {
            $this->Csz_admin_model->removeData('email_logs', 'email_logs_id', $this->uri->segment(4));
            $this->db->cache_delete_all();
            $this->session->set_flashdata('error_message','<div class="alert alert-success" role="alert">'.$this->lang->line('success_message_alert').'</div>');
        }
        //Return to languages list
        redirect($this->csz_referrer->getIndex(), 'refresh');
    }

    public function login() {
        admin_helper::login_already($this->session->userdata('admin_email'));
        //Load the form helper

        $this->template->setSub('error', '');
        $this->load->helper('form');
        $this->template->loadSub('admin/login');
    }

    public function loginCheck() {
        admin_helper::login_already($this->session->userdata('admin_email'));
        //Load the form validation library
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', $this->lang->line('login_email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('password', $this->lang->line('login_password'), 'trim|required|min_length[4]|max_length[32]');
        $this->form_validation->set_message('valid_email', $this->lang->line('valid_email'));
        $this->form_validation->set_message('required', $this->lang->line('required'));
        $this->form_validation->set_message('min_length', $this->lang->line('min_length'));
        $this->form_validation->set_message('max_length', $this->lang->line('max_length'));
        if ($this->form_validation->run() == FALSE) {
            $this->login();
        }else{
            $email = $this->Csz_model->cleanEmailFormat($this->input->post('email', TRUE));
            $password = sha1(md5($this->input->post('password', TRUE)));
            $result = $this->Csz_admin_model->login($email, $password);
            if ($result == 'SUCCESS') {
                $this->Csz_model->saveLogs($email, 'Backend Login Successful!', $result);
                if($this->session->userdata('cszblogin_cururl')){
                    redirect($this->session->userdata('cszblogin_cururl'), 'refresh');
                }else{
                    redirect(BASE_URL.'/admin', 'refresh');
                }
            } else {
                $this->Csz_model->saveLogs($email, 'Backend Login Invalid!', $result);
                $this->template->setSub('error', $result);
                $this->load->helper('form');
                $this->template->loadSub('admin/login');
            }
        } 
    }

    public function logout() {
        $data = array(
            'user_admin_id',
            'admin_name',
            'admin_email',
            'admin_type',
            'admin_visitor',
            'session_id',
            'admin_logged_in',
        );
        $this->session->unset_userdata($data);
        redirect(BASE_URL.'/admin', 'refresh');
    }

    public function social() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        //Load the form helper
        $this->csz_referrer->setIndex();
        $this->load->helper('form');

        $this->template->setSub('social', $this->Csz_admin_model->getSocial());
        $this->template->loadSub('admin/social');
    }

    public function updateSocial() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        admin_helper::chkVisitor($this->session->userdata('user_admin_id'));
        $this->Csz_admin_model->updateSocial();
        $this->db->cache_delete_all();
        $this->session->set_flashdata('error_message','<div class="alert alert-success" role="alert">'.$this->lang->line('success_message_alert').'</div>');
        redirect($this->csz_referrer->getIndex(), 'refresh');
    }
    
    public function genSitemap() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        admin_helper::is_not_admin($this->session->userdata('admin_type'));
        admin_helper::chkVisitor($this->session->userdata('user_admin_id'));
        $this->load->model('Csz_sitemap');
        $this->Csz_sitemap->runSitemap();
        $this->session->set_flashdata('error_message','<div class="alert alert-success" role="alert">'.$this->lang->line('success_message_alert').'</div>');
        redirect($this->csz_referrer->getIndex(), 'refresh');
    }

    public function settings() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        admin_helper::is_not_admin($this->session->userdata('admin_type'));
        //Load the form helper
        $this->csz_referrer->setIndex();
        $this->load->helper('form');
        $this->load->helper('directory');
        $this->load->model('Csz_sitemap');
        $this->template->setSub('themesdir', directory_map(APPPATH . '/views/templates/', 1));
        $this->template->setSub('langdir', directory_map(APPPATH . '/language/', 1));
        $this->template->setSub('sitemaptime', $this->Csz_sitemap->getFileTime());

        $this->template->setSub('settings', $this->Csz_admin_model->load_config());
        $this->template->loadSub('admin/settings');
    }

    public function updateSettings() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        admin_helper::is_not_admin($this->session->userdata('admin_type'));
        admin_helper::chkVisitor($this->session->userdata('user_admin_id'));
        $this->Csz_admin_model->updateSettings();
        $this->db->cache_delete_all();
        $this->session->set_flashdata('error_message','<div class="alert alert-success" role="alert">'.$this->lang->line('success_message_alert').'</div>');
        redirect($this->csz_referrer->getIndex(), 'refresh');
    }
    
    public function uploadIndex() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        //Load the form helper
        $this->csz_referrer->setIndex();
        $this->load->helper('form');
        $this->load->library('pagination');
        $search_arr = '';
        if($this->input->get('search')){
            $search_arr.= ' 1=1 ';
            if($this->input->get('search')){
                $search_arr.= " AND year LIKE '%".$this->input->get('search', TRUE)."%' OR remark LIKE '%".$this->input->get('search', TRUE)."%' OR file_upload LIKE '%".$this->input->get('search', TRUE)."%'";
            }
        }

        // Pages variable
        $result_per_page = 20;
        $total_row = $this->Csz_model->countData('upload_file', $search_arr);
        $num_link = 10;
        $base_url = BASE_URL . '/admin/uploadindex/';

        // Pageination config
        $this->Csz_admin_model->pageSetting($base_url, $total_row, $result_per_page, $num_link);
        ($this->uri->segment(3)) ? $pagination = ($this->uri->segment(3)) : $pagination = 0;
        
        $this->template->setSub('showfile', $this->Csz_admin_model->getIndexData('upload_file', $result_per_page, $pagination, 'timestamp_create', 'desc', $search_arr));
        $this->template->loadSub('admin/upload_index');
    }
    
    public function uploadDownload() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        //Load the form helper
        if($this->uri->segment(3)){
            $file = $this->Csz_model->getValue('file_upload', 'upload_file', "upload_file_id", $this->uri->segment(3), 1);
            $path = FCPATH."/photo/upload/".$file->file_upload;
            $this->load->helper('file');
            $data = read_file($path);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $filename = strtolower(pathinfo($path, PATHINFO_FILENAME));
            $this->load->helper('download');
            force_download($filename.'.'.$ext, $data);
        }else{
            redirect($this->csz_referrer->getIndex(), 'refresh');
        }
    }

    public function uploadIndexSave() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        admin_helper::chkVisitor($this->session->userdata('user_admin_id'));
        $path = FCPATH . "/photo/upload/";
        $filedel = $this->input->post('filedel');
        if(isset($filedel)){
            foreach ($filedel as $value) {
                if ($value) {
                    $filename = $this->Csz_model->getValue('file_upload', 'upload_file', 'upload_file_id', $value, 1);
                    if($filename->file_upload){
                        @unlink($path . $filename->file_upload);
                    }
                    $this->Csz_admin_model->removeData('upload_file', 'upload_file_id', $value);
                }
            }
        }
        $remark = $this->input->post('remark',TRUE);
        if(isset($remark)){
            foreach ($remark as $key => $value) {
                if ($value && $key) {
                    $this->db->set('remark', $value, TRUE);
                    $this->db->set('timestamp_update', 'NOW()', FALSE);
                    $this->db->where('upload_file_id', $key);
                    $this->db->update('upload_file');
                }
            }
        }
        $this->db->cache_delete_all();
        $this->session->set_flashdata('error_message','<div class="alert alert-success" role="alert">'.$this->lang->line('success_message_alert').'</div>');
        redirect($this->csz_referrer->getIndex(), 'refresh');
    }

    public function htmlUpload() {
        admin_helper::is_logged_in($this->session->userdata('admin_email'));
        admin_helper::chkVisitor($this->session->userdata('user_admin_id'));
        if ($this->uri->segment(3)) {
            $year = $this->uri->segment(3);
        } else {
            $year = date("Y");
        }
        $path = FCPATH . "/photo/upload/";
        $files = $_FILES;
        $cpt = count($_FILES['files']['name']);
        for($i=0; $i<$cpt; $i++) {   
            if($files['files']['name'][$i]){
                $file_id = time() . "_" . rand(1111, 9999);
                $photo_name = $files['files']['name'][$i];
                $photo = $files['files']['tmp_name'][$i];
                $file_id1 = $this->Csz_admin_model->file_upload($photo, $photo_name, '', $path, $file_id, '', $year);
                if ($file_id1) {
                    $this->Csz_admin_model->insertFileUpload($year, $file_id1);
                }
            }
        }
        $this->db->cache_delete_all();
        $this->session->set_flashdata('error_message','<div class="alert alert-success" role="alert">'.$this->lang->line('success_message_alert').'</div>');
        redirect($this->csz_referrer->getIndex(), 'refresh');
    }

}
