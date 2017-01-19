<?php
namespace OctOAuth\OAuth2\Client\Provider\Token;

use Abraham\TwitterOAuth\Token;

/** Implement this interface as required to allow temporary storage of OAuth1 request tokens.
 *
 * @see OAuth1TemporaryTokenStoreOnSession
 */
interface OAuth1TemporaryTokenStore
{
    /**
     * Save a new token for this session, overwriting any previous ones.
     *
     * @param Token $token The token to save.
     */
    public function saveToken(Token $token);

    /**
     * Retrieve the stored token
     *
     * @return Token The token retrieved
     */
    public function getToken(): Token;

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
