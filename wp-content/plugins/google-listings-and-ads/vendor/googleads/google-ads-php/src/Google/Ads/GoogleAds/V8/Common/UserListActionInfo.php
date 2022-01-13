<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/common/user_lists.proto

namespace Google\Ads\GoogleAds\V8\Common;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Represents an action type used for building remarketing user lists.
 *
 * Generated from protobuf message <code>google.ads.googleads.v8.common.UserListActionInfo</code>
 */
class UserListActionInfo extends \Google\Protobuf\Internal\Message
{
    protected $user_list_action;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $conversion_action
     *           A conversion action that's not generated from remarketing.
     *     @type string $remarketing_action
     *           A remarketing action.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V8\Common\UserLists::initOnce();
        parent::__construct($data);
    }

    /**
     * A conversion action that's not generated from remarketing.
     *
     * Generated from protobuf field <code>string conversion_action = 3;</code>
     * @return string
     */
    public function getConversionAction()
    {
        return $this->readOneof(3);
    }

    public function hasConversionAction()
    {
        return $this->hasOneof(3);
    }

    /**
     * A conversion action that's not generated from remarketing.
     *
     * Generated from protobuf field <code>string conversion_action = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setConversionAction($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(3, $var);

        return $this;
    }

    /**
     * A remarketing action.
     *
     * Generated from protobuf field <code>string remarketing_action = 4;</code>
     * @return string
     */
    public function getRemarketingAction()
    {
        return $this->readOneof(4);
    }

    public function hasRemarketingAction()
    {
        return $this->hasOneof(4);
    }

    /**
     * A remarketing action.
     *
     * Generated from protobuf field <code>string remarketing_action = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setRemarketingAction($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(4, $var);

        return $this;
    }

    /**
     * @return string
     */
    public function getUserListAction()
    {
        return $this->whichOneof("user_list_action");
    }

}

