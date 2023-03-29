<?php


namespace App\Huawei;


use App\Huawei\PushKit\Android\AndroidConfig;
use App\Huawei\PushKit\Android\AndroidNotification;
use App\Huawei\PushKit\Android\Badge;
use App\Huawei\PushKit\Android\ClickAction;
use App\Huawei\PushKit\Android\LightSetting;
use App\Huawei\PushKit\Android\LightSettingColor;
use App\Huawei\PushKit\APNS\Alert;
use App\Huawei\PushKit\APNS\ApnsConfig;
use App\Huawei\PushKit\APNS\ApnsHeaders;
use App\Huawei\PushKit\APNS\ApnsHmsOptions;
use App\Huawei\PushKit\APNS\Aps;
use App\Huawei\PushKit\InstanceApp\InstanceAppConfig;
use App\Huawei\PushKit\InstanceApp\InstanceAppPushbody;
use App\Huawei\PushKit\InstanceApp\InstanceAppRingtone;
use App\Huawei\PushKit\PushMessage;
use App\Huawei\PushKit\WebPush\WebPushConfig;
use App\Huawei\PushKit\WebPush\WebPushHeaders;
use App\Huawei\PushKit\WebPush\WebPushHmsOptions;
use App\Huawei\PushKit\WebPush\WebPushNotification;
use App\Huawei\PushKit\WebPush\WebPushNotificationAction;
use Illuminate\Support\Facades\Log;

class HuaweiPush
{
    // ordinal app
    private $appid;
    private $appsecret;
    // FOR PUSH MSG NOTIFICATION,PASSTHROUGH TOPIC/TOKEN/CONDITION
    public  $hw_push_token_key;
    // FOR APN
    public $apn_push_token_key;
    // FOR WEBPUSH
    public $webpush_push_token_key;

    // fast app
    private $fast_appid;
    private $fast_appsecret;
    // fast app token
    public  $fast_push_token;


    private $hw_token_server;
    private $hw_push_server;
    private $log_suffix_show_start = ".............................";
    private $log_suffix_show_end = "-----------------------------";
    private $push_msg_type;
    private $default_topic = 'defaultTopic';

    private $str_len = 35;

    public function __construct()
    {
        $pushConfig = new HuaweiConfig;
        $this->appsecret = $pushConfig->HW_APPSECRET;
        $this->appid = $pushConfig->HW_APPID;
        $this->hw_token_server = $pushConfig->HW_TOKEN_SERVER;
        $this->hw_push_server = $pushConfig->HW_PUSH_SERVER;
        $this->hw_push_token_key = $pushConfig->HW_PUSH_TOKEN_ARR;
        $this->apn_push_token_key = $pushConfig->APN_PUSH_TOKEN_ARR;
        $this->webpush_push_token_key = $pushConfig->WEBPUSH_PUSH_TOKEN_ARR;
        $this->fast_appsecret = $pushConfig->HW_FAST_APPSECRET;
        $this->fast_appid = $pushConfig->HW_FAST_APPID;
        $this->fast_push_token = $pushConfig->HW_FAST_PUSH_TOKEN;

    }

    private function createApplication($application_server)
    {
        if ($this->push_msg_type == Constants::PUSHMSG_FASTAPP_MSG_TYPE){
            $application = new HuaweiPushKit($this->fast_appid, $this->fast_appsecret, $this->hw_token_server, $application_server);
            return $application;
        }
        $application = new HuaweiPushKit($this->appid, $this->appsecret, $this->hw_token_server, $application_server);
        return $application;
    }

    function sendPushMsgRealMessage($message,$push_msg_type="")
    {
        if (!empty($push_msg_type)) {
            $this->push_msg_type = $push_msg_type;
        }
        $application_server = $this->hw_push_server;
        $application = $this->createApplication($application_server);

        $application->push_send_msg($message);
    }

    function sendPushMsgMessageByMsgType($msg_type, $topic = "")
    {
        $application_server = $this->hw_push_server;

        $this->push_msg_type = $msg_type;
        $message = $this->getMessageByMsgType($msg_type);

        $application = $this->createApplication($application_server);

        $application->push_send_msg($message->getFields());
    }

    function sendPushTopicMsgMessage($topic = "")
    {
        if (empty($topic)) {
            $topic = $this->default_topic;
        }
        $testTopicCommonSample = new TestTopicCommonSample($topic);

        // subscribe msg
        $testTopicCommonSample->sendTopicMessage(Constants::TOPIC_SUBSCRIBE_MSG_TYPE);
        // query subscribe msg
        $testTopicCommonSample->sendTopicMessage(Constants::TOPIC_SUBSCRIBE_QUERY_MSG_TYPE);
    }

    /**
     * $result==>{"msg":"success","code":"80000000","requestId":"157561883923402813000201",
     * "topics":[{"name":"defaultTopic","addDate":"2019-12-06"},
     * {"name":"push-test","addDate":"2019-12-06"},
     * {"name":"targetTopic","addDate":"2019-12-06"},
     * {"name":"weather","addDate":"2019-12-06"}]}
     */
    private function isTopicInTopicList($result, $topic)
    {
        if (empty($result)) {
            return FALSE;
        }

        $arrResult = json_decode(json_encode($result), true);

        if (empty($arrResult["code"])) {
            return FALSE;
        }

        if (! in_array($arrResult["code"], array(
            "80000000",
            80000000
        ))) {
            return FALSE;
        }
        if (empty($arrResult["topics"])) {
            return FALSE;
        }

        $topicArr = $arrResult["topics"];
        if (empty($topicArr)) {
            return FALSE;
        }

        foreach ($topicArr as $topicObject) {
            if ($topicObject["name"] == $topic) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function getDefaultAndroidNotificationContent($titel)
    {
        $prefixTitleData = '';
        switch ($this->push_msg_type) {
            case Constants::PUSHMSG_NOTIFICATION_MSG_TYPE:
            {
                $prefixTitleData = ' notification ';
                break;
            }
            case Constants::PUSHMSG_PASS_THROUGHT_MSG_TYPE:
            {
                $prefixTitleData = ' passthrough ';
                break;
            }

            case Constants::PUSHMSG_FASTAPP_MSG_TYPE:
            {
                $prefixTitleData = ' fastapp ';
                break;
            }
            case Constants::PUSHMSG_TOPIC_MSG_TYPE:
            {
                $prefixTitleData = ' topic ';
                break;
            }
            case Constants::PUSHMSG_CONDITION_MSG_TYPE:
            {
                $prefixTitleData = ' condition ';
                break;
            }

            case Constants::APN_MSG_TYPE:
            {
                $prefixTitleData = ' apn ';
                break;
            }
            case Constants::WEB_PUSH_MSG_TYPE:
            {
                $prefixTitleData = ' webpush ';
                break;
            }
        }

        return $prefixTitleData . $titel . $prefixTitleData;
    }

    private function createAndroidNotification()
    {
        // generate click_action msg body for android notification-3-click_action
        $click_action = new ClickAction();
        $click_action->type(2);
        $click_action->type(1);

        $click_action->intent("#Intent;compo=com.rvr/.Activity;S.W=U;end");
        $click_action->action("test add");
        $click_action->url("https://www.baidu.com");
        $click_action->rich_resource("test rich resource");
        $click_action->buildFields();

        // generate Badge for android notification-3-badge
        $badge = new Badge();
        $badge->add_num(99);
        $badge->setclass("Classic");
        $badge->set_num(99);
        $badge->buildFields();

        // generate Light Settings for android notification-3-badge
        $lightSetting = new LightSetting();
        $lightSetting->light_on_duration("3.5");
        $lightSetting->light_off_duration("5S");
        // set light setting color
        $LightSettingColor = new LightSettingColor();
        $LightSettingColor->setgenFullcolor(0, 0, 1, 1);
        $LightSettingColor->buildFields();
        $lightSetting->color($LightSettingColor->getFields());
        $lightSetting->buildFields();

        // 构建android notification消息体-2 for android config
        $android_notification = new AndroidNotification();
        $android_notification->title($this->getDefaultAndroidNotificationContent("default hw title "));
        $android_notification->body($this->getDefaultAndroidNotificationContent("default hw body"));
        $android_notification->icon("https://res.vmallres.com/pimages//common/config/logo/SXppnESYv4K11DBxDFc2.png");
        $android_notification->color("#AACCDD");
        $android_notification->sound("https://att.chinauui.com/day_120606/20120606_7fcf2235b44f1eab0b4dadtAkAGMTBHK.mp3");
        $android_notification->tag("tagBoom");
        $android_notification->body_loc_key("M.String.body");
        $android_notification->body_loc_args(array(
            "Boy",
            "Dog"
        ));
        $android_notification->title_loc_key("M.String.title");
        $android_notification->title_loc_args(array(
            "Girl",
            "Cat"
        ));
        $android_notification->channel_id("RingRing");
        $android_notification->notify_summary("Some Summary");
        $android_notification->image("https://developer-portalres-drcn.dbankcdn.com/system/modules/org.opencms.portal.template.core/resources/images/icon_Promotion.png");
        $android_notification->style(0);
        $android_notification->big_title("Big Boom Title");
        $android_notification->big_body("Big Boom Body");
        $android_notification->auto_clear(86400000);
        $android_notification->notify_id(486);
        $android_notification->group("Espace");
        $android_notification->importance(NotificationPriority::NOTIFICATION_PRIORITY_NORMAL);
        $android_notification->ticker("i am a ticker");
        $android_notification->auto_cancel(false);
        $android_notification->when("2019-11-05");
        $android_notification->use_default_vibrate(true);
        $android_notification->use_default_light(false);
        $android_notification->visibility("PUBLIC");
        $android_notification->foreground_show(true);
        $android_notification->vibrate_config(array(
            "1.5",
            "2.000000001",
            "3"
        ));
        $android_notification->click_action($click_action->getFields());
        $android_notification->badge($badge->getFields());
        $android_notification->light_settings($lightSetting->getFields());

        $android_notification->buildFields();

        return $android_notification;
    }

    private function createAndroidConfig()
    {
        $android_notification = $this->createAndroidNotification();

        $android_config = new AndroidConfig();
        $android_config->collapse_key(- 1);
        $android_config->urgency(AndroidConfigDeliveryPriority::PRIORITY_HIGH);
        $android_config->ttl("1448s");
        $android_config->bi_tag("Trump");
        if ($this->push_msg_type == Constants::PUSHMSG_FASTAPP_MSG_TYPE) {
            $android_config->fast_app_target(1);
        }
        else{
            $android_config->notification($android_notification->getFields());
        }
        $android_config->buildFields();
        return $android_config;
    }

    private function createNotification()
    {
        $notification = new PushKit\Notification\Notification("Big News", "This is a Big News!", "https://res.vmallres.com/pimages//common/config/logo/SXppnESYv4K11DBxDFc2_0.png");
        $notification->buildFields();
        return $notification;
    }

    private function createApnsConfig()
    {
        // ApnsHeaders
        $apnsHeaders = new ApnsHeaders();
        $apnsHeaders->apns_topic("hmspush");
        $apnsHeaders->apns_priority(ApnConstant::ANP_PRIORITY_SEND_IMMEDIATELY);
        $apnsHeaders->buildFields();

        // ApnHmsOptions
        $apnsHmsOptions = new ApnsHmsOptions();
        $apnsHmsOptions->target_user_type(ApnConstant::APN_TARGET_USER_TEST_USER);
        $apnsHmsOptions->buildFields();

        // Aps
        // Alert
        $alert = new Alert();
        $alert->title("hw default ios message title");
        $alert->body("hw default ios message body");
        $alert->action_loc_key("PLAY");
        $alert->buildFields();

        $aps = new Aps();
        $aps->alert($alert->getFields());
        $aps->badge(5);
        $aps->buildFields();

        $apnsConfig = new ApnsConfig();
        $apnsConfig->headers($apnsHeaders->getFields());
        $apnsConfig->hms_options($apnsHmsOptions->getFields());

        $apn_payload["aps"] = $aps->getFields();
        $apn_payload["acme1"] = "bar";
        $apn_payload["acme2"] = array(
            "bang",
            "whiz"
        );
        $apnsConfig->payload($apn_payload);

        $apnsConfig->buildFields();

        return $apnsConfig;
    }

    private function createWebPush()
    {
        $webPushConfig = new WebPushConfig();
        $webPushConfig->data("test webpush data");

        $webPushHeaders = new WebPushHeaders();
        $webPushHeaders->topic("12313ceshi");
        $webPushHeaders->ttl("990");
        $webPushHeaders->urgency(Constants::WEBPUSH_URGENCY_VERY_LOW);
        $webPushHeaders->buildFields();
        $webPushConfig->headers($webPushHeaders->getFields());

        $webPushHmsOptions = new WebPushHmsOptions();
        $webPushHmsOptions->link("https://www.huawei.com/");
        $webPushHmsOptions->buildFields();
        $webPushConfig->hmsOptions($webPushHmsOptions->getFields());

        $webPUshNotionfication = new WebPushNotification();
        $webPUshNotionfication->title("notication string");
        $webPUshNotionfication->body("web push body");
        $webPUshNotionfication->icon("https://developer-portalres-drcn.dbankcdn.com/system/modules/org.opencms.portal.template.core/resources/images/icon_Promotion.png");
        $webPUshNotionfication->image("https://developer-portalres-drcn.dbankcdn.com/system/modules/org.opencms.portal.template.core/resources/images/icon_Promotion.png");
        $webPUshNotionfication->lang("string");
        $webPUshNotionfication->tag("string");
        $webPUshNotionfication->badge("string");
        $webPUshNotionfication->dir("auto");
        $webPUshNotionfication->vibrate(array(
            1,
            2,
            3
        ));
        $webPUshNotionfication->renotify(false);
        $webPUshNotionfication->require_interaction(false);
        $webPUshNotionfication->silent(false);
        $webPUshNotionfication->timestamp(1545201266);
        $webPushNotificationAction = new WebPushNotificationAction();
        $webPushNotificationAction->title("string");
        $webPushNotificationAction->action("123");
        $webPushNotificationAction->icon("https://developer-portalres-drcn.dbankcdn.com/system/modules/org.opencms.portal.template.core/resources/images/icon_Promotion.png");
        $webPushNotificationAction->buildFields();
        $webPUshNotionfication->actions(array(
            $webPushNotificationAction->getFields()
        ));
        $webPUshNotionfication->buildFields();
        $webPushConfig->notification($webPUshNotionfication->getFields());
        $webPushConfig->buildFields();

        return $webPushConfig;
    }

    private function getMessageByMsgType($msg_type)
    {
        switch ($msg_type) {
            case Constants::PUSHMSG_NOTIFICATION_MSG_TYPE:
            {
                return $this->createNotificationMsg();
            }
            case Constants::PUSHMSG_PASS_THROUGHT_MSG_TYPE:
            {
                return $this->createPassThroughMsg();
            }

            case Constants::PUSHMSG_FASTAPP_MSG_TYPE:
            {
                return $this->createFastAppMsg();
            }
            case Constants::PUSHMSG_TOPIC_MSG_TYPE:
            {
                return $this->createTopicMsg();
            }
            case Constants::PUSHMSG_CONDITION_MSG_TYPE:
            {
                return $this->createConditionMsg();
            }

            case Constants::APN_MSG_TYPE:
            {
                return $this->createApnsMsg();
            }
            case Constants::WEB_PUSH_MSG_TYPE:
            {
                return $this->createWebPushMsg();
            }
        }
    }

    private function createFastAppConfigNotificationData(){
        $instanceAppConfig = new InstanceAppConfig();
        $instanceAppConfig->pushtype(0);

        $instanceAppPushbody = new InstanceAppPushbody();
        $instanceAppPushbody->title("test fast app");
        $instanceAppPushbody->description("test fast app description");
        $instanceAppPushbody->page("/");
        $instanceAppPushbody->params(array(
            "key1"=>"test1",
            "key2"=>"test2"
        ));

        $instanceAppRingtone = new InstanceAppRingtone();
        $instanceAppRingtone->breathLight(true);
        $instanceAppRingtone->vibration(true);
        $instanceAppRingtone->buildFields();

        $instanceAppPushbody->ringtone($instanceAppRingtone->getFields());
        $instanceAppPushbody->buildFields();

        $instanceAppConfig->pushbody($instanceAppPushbody->getFields());
        $instanceAppConfig->buildFields();

        return $instanceAppConfig;

    }

    private function createFastAppConfigPassThroughData(){
        $instanceAppConfig = new InstanceAppConfig();
        $instanceAppConfig->pushtype(1);

        $instanceAppPushbody = new InstanceAppPushbody();
        $instanceAppPushbody->messageId("111110001");
        $instanceAppPushbody->data("hw default passthroug test");
        $instanceAppPushbody->buildFields();

        $instanceAppConfig->pushbody($instanceAppPushbody->getFields());
        $instanceAppConfig->buildFields();

        return $instanceAppConfig;

    }

    private function createFastAppMsg()
    {
        $message = new PushMessage();

        $message->data($this->createFastAppConfigNotificationData()->getFields());

        $message->android($this->createAndroidConfig()
            ->getFields());

        $message->token(array(
            $this->fast_push_token
        ));

        $message->buildFields();
        return $message;
    }

    private function createNotificationMsg()
    {
        $message = new PushMessage();

        $message->android($this->createAndroidConfig()
            ->getFields());
        $message->notification($this->createNotification()
            ->getFields());

        $message->token(array(
            $this->hw_push_token_key
        ));

        $message->buildFields();
        return $message;
    }

    private function createTopicMsg()
    {
        $message = new PushMessage();

        $message->android($this->createAndroidConfig()
            ->getFields());
        // $message->notification($this->createNotification()->buildFields());

        $message->topic($this->default_topic);

        $message->buildFields();
        return $message;
    }

    private function createConditionMsg()
    {
        $message = new PushMessage();

        $message->android($this->createAndroidConfig()
            ->getFields());
        // $message->notification($this->createNotification()->buildFields());
        $message->condition("'defaultTopic' in topics");
        // $message->condition("'weather' in topics || ('TopicB' in topics && 'TopicC' in topics)");

        $message->buildFields();
        return $message;
    }

    private function createPassThroughMsg()
    {
        $message = new PushMessage();

        $message->data("1111");
        $message->token(array(
            $this->hw_push_token_key
        ));

        $message->buildFields();
        return $message;
    }

    private function createApnsMsg()
    {
        $message = new PushMessage();
        $apnsConfig = $this->createApnsConfig();
        $message->apns($apnsConfig->getFields());

        $message->token(array(
            $this->apn_push_token_key
        ));
        $message->buildFields();

        return $message;
    }

    private function createWebPushMsg()
    {
        $message = new PushMessage();

        $message->webpush($this->createWebPush()
            ->getFields());
        $message->token(array(
            $this->webpush_push_token_key
        ));

        $message->buildFields();

        return $message;
    }
}
