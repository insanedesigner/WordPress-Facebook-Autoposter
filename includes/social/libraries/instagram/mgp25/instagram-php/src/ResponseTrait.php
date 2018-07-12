<?php

namespace InstagramAPI;

use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * Standard implementation traits for ResponseInterface.
 *
 * Remember that all response-classes must "extend AutoPropertyHandler",
 * "implements ResponseInterface", and "use ResponseTrait", otherwise they
 * won't work properly.
 */
trait ResponseTrait
{
    /** @var string */
    public $status;
    /** @var string */
    public $message;
    /** @var \InstagramAPI\Response\Model\_Message[] */
    public $_messages; // NOTE: Full classpath is needed above for JSONMapper!
    /** @var mixed */
    public $fullResponse;
    /** @var HttpResponseInterface */
    public $httpResponse;

    /**
     * Checks if the response was successful.
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->status === 'ok'; // Can be: 'ok', 'fail'
    }

    /**
     * Sets the status.
     *
     * @param string|null $status
     */
    public function setStatus(
        $status)
    {
        $this->status = $status;
    }

    /**
     * Gets the status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Checks if a status value exists.
     *
     * @return bool
     */
    public function isStatus()
    {
        return $this->status !== null;
    }

    /**
     * Sets the message.
     *
     * @param string|null $message
     */
    public function setMessage(
        $message)
    {
        $this->message = $message;
    }

    /**
     * Gets the message.
     *
     * @throws \Exception If the message object is of an unsupported type.
     *
     * @return string|null A message string if one exists, otherwise NULL.
     */
    public function getMessage()
    {
        // Instagram's API usually returns a simple error string. But in some
        // cases, they instead return a subarray of individual errors, in case
        // of APIs that can return multiple errors at once.
        //
        // Uncomment this if you want to test multiple error handling:
        // $json = '{"status":"fail","message":{"errors":["Select a valid choice. 0 is not one of the available choices."]}}';
        // $json = '{"status":"fail","message":{"errors":["Select a valid choice. 0 is not one of the available choices.","Another error.","One more error."]}}';
        // $obj = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
        // $this->message = $obj->message;

        if ($this->message === null || is_string($this->message)) {
            // Single error string or nothing at all.
            return $this->message;
        } elseif (is_object($this->message)) {
            // Multiple errors in an "errors" subarray.
            $vars = get_object_vars($this->message);
            if (count($vars) == 1 && isset($vars['errors']) && is_array($vars['errors'])) {
                // Add "Multiple Errors" prefix if the response contains more than one.
                // But most of the time, there will only be one error in the array.
                $str = (count($vars['errors']) > 1 ? 'Multiple Errors: ' : '');
                $str .= implode(' AND ', $vars['errors']); // Assumes all errors are strings.
                return $str;
            } else {
                throw new \Exception('Unknown message object. Expected errors subarray but found something else. Please submit a ticket about needing an Instagram-API library update!');
            }
        } else {
            throw new \Exception('Unknown message type. Please submit a ticket about needing an Instagram-API library update!');
        }
    }

    /**
     * Checks if a message value exists.
     *
     * @return bool
     */
    public function isMessage()
    {
        return $this->message !== null;
    }

    /**
     * Sets the special API status messages.
     *
     * @param Response\Model\_Message[]|null $_messages
     */
    public function set_Messages(
        $_messages)
    {
        $this->_messages = $_messages;
    }

    /**
     * Gets the special API status messages.
     *
     * This can exist in any Instagram API response, and carries special status
     * information. Known messages: "fb_needs_reauth", "vkontakte_needs_reauth",
     * "twitter_needs_reauth", "ameba_needs_reauth", "update_push_token".
     *
     * @return Response\Model\_Message[]|null Messages if any, otherwise NULL.
     */
    public function get_Messages()
    {
        return $this->_messages;
    }

    /**
     * Checks if any API status messages value exists.
     *
     * @return bool
     */
    public function is_Messages()
    {
        return $this->_messages !== null;
    }

    /**
     * Sets the full response.
     *
     * @param mixed $response
     */
    public function setFullResponse(
        $response)
    {
        $this->fullResponse = $response;
    }

    /**
     * Gets the full response.
     *
     * @return mixed
     */
    public function getFullResponse()
    {
        return $this->fullResponse;
    }

    /**
     * Checks if a full response value exists.
     *
     * @return bool
     */
    public function isFullResponse()
    {
        return $this->fullResponse !== null;
    }

    /**
     * Gets the HTTP response.
     *
     * @return HttpResponseInterface
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * Sets the HTTP response.
     *
     * @param HttpResponseInterface $response
     */
    public function setHttpResponse(
        HttpResponseInterface $response)
    {
        $this->httpResponse = $response;
    }

    /**
     * Checks if an HTTP response value exists.
     *
     * @return bool
     */
    public function isHttpResponse()
    {
        return $this->httpResponse !== null;
    }
}
