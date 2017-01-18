<?php
namespace OctOAuth\OAuth2\Client\Provider\Twitter;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use stdClass;

class TwitterResourceOwner implements ResourceOwnerInterface
{
    /** @var string */
    private $id;
    /** @var array */
    private $values;

    /**
     * TwitterResourceOwner constructor.
     *
     * @param string   $id     The resource owner's ID
     * @param stdClass $values The response from the call to twitter
     */
    public function __construct(string $id, stdClass $values)
    {
        $this->id = $id;
        // convert the object to a nested array
        $this->values = json_decode(json_encode($values), true);
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the user's name
     *
     * @return string The user's name (e.g. 'Finanscapes.com')
     */
    public function getName(): string
    {
        return $this->values["name"];
    }

    /**
     * Get the user's screen name
     *
     * @return string The user's screen name (e.g 'finanscapes')
     */
    public function getScreenName(): string
    {
        return $this->values["screen_name"];
    }

    /**
     * See https://apps.twitter.com/app/XXX/permissions (replacing XXX with your app ID) for details
     * on how to set the app permissions to return the user's email address
     *
     * @return string|null
     */
    public function getEmail()
    {
        if (isset($this->values["email"])) {
            return $this->values["email"];
        } else {
            return null;
        }
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }
}
