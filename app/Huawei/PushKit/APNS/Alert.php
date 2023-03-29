<?php
/**
Copyright 2020. Huawei Technologies Co., Ltd. All rights reserved.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 */

/**
 * function: Alert => =>PushMessage(apns) for ios channel
 */
namespace App\Huawei\PushKit\APNS;


class Alert {

    private $title;
    private $body;
    private $title_loc_key;
    private $title_loc_args;
    private $action_loc_key;
    private $loc_key;
    private $loc_args;
    private $launch_image;
    private $fields;

    public function title($value) {
        $this->title = $value;
    }
    public function body($value) {
        $this->body = $value;
    }
    public function title_loc_key($value) {
        $this->title_loc_key = $value;
    }
    public function title_loc_args($value) {
        $this->title_loc_args = $value;
    }
    public function action_loc_key($value) {
        $this->action_loc_key = $value;
    }
    public function loc_key($value) {
        $this->loc_key = $value;
    }
    public function loc_args($value) {
        $this->loc_args = $value;
    }
    public function launch_image($value) {
        $this->launch_image = $value;
    }
    public function getFields()
    {
        return $this->fields;
    }

    public function buildFields()
    {
        $keys = array(
            'title',
            'body',
            'title-loc-key',
            'title-loc-args',
            'action-loc-key',
            'loc-key',
            'loc-args',
            'launch-image'
        );
        foreach ($keys as $key) {

            $value = str_replace('-', '_', $key);

            if (isset($this->$value)) {
                $this->fields[$key] = $this->$value;
            }
        }
    }
}
