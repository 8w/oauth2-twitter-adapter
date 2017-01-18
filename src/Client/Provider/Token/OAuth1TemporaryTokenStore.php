<?php
namespace OctOAuth\OAuth2\Client\Provider\Token;

/** Implement this interface as required to allow temporary storage of OAuth1 request tokens.
 *
 * @see OAuth1TemporaryTokenStoreOnSession
 */
interface OAuth1TemporaryTokenStore
{
    /**
     * Save a new token for this session, overwriting any previous ones.
     *
     * @param OAuth1TemporaryToken $token The token to save.
     */
    public function saveToken(OAuth1TemporaryToken $token);

    /**
     * Retrieve the stored token
     *
     * @return OAuth1TemporaryToken The token retrieved
     */
    public function getToken(): OAuth1TemporaryToken;

    /**
     * Delete the stored token, if one exists.
     *
     * NOTE: There is no guarantee this method will be called, so some sort of garbage collection
     * may be prudent.
     *
     * @return void
     */
    public function clearToken();
}
