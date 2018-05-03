<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/22/18
 * Time: 2:33 PM
 */

namespace app\controllers;

require_once "app/controllers/BaseController.php";
require_once "app/models/Unit.php";
require_once "app/models/Person.php";

use lib\Controller;
use app\models\User;
use app\models\Comment;
use app\models\Message;

/**
 * Class SampleController
 * @package app\controllers
 *
 * Sample controller. Used for hosting effectively-static pages.
 * Prefix: '/'
 * Responsibilities:
 *  - Serving site-level pages or pages with no other sensible controller
 */
class SiteController extends BaseController
{
    /**
     * Factory function, instantiates the controller (called by the server upon dispatch)
     */
    public static function init(): Controller
    {
        return new SiteController();
    }

    /**
     * @return array List of routes for this controller.
     */
    public function routes(): array
    {
        return [
            self::route("GET", "/about", 'about'),
            self::route("GET", "/credits", 'credits'),
            self::route("GET", "/", 'index')
        ];
    }

    /**
     * Full path: '/credits'
     *
     * Displays the credits page
     */
    public function credits($params)
    {
        require "app/views/credits.phtml";
    }

    /**
     * Full path: '/about'
     *
     * Displays the about page
     */
    public function about($params)
    {
        require "app/views/about.phtml";
    }

    /**
     * Full path: '/' (site root)
     *
     * Displays the site homepage.
     */
    public function index($params)
    {
        if ($this->is_logged_in()) {
            $token = $this->require_authentication();

            if ($token == null) {
                $this->error404($params[0]);
                return;
            }

            $db = $this->getDBConn();

            // Fetch user info from token
            $user = $token->getUser();

          // Fetch followers and followees
          $following = User::fetchFollowedUsers($db, $user->getUserId());
          if ($following == null) {
              $following = [];
          }

          $followers = User::fetchFollowingUsers($db, $user->getUserId());
          if ($followers == null) {
              $followers = [];
          }

          // Fetch new comments for activity feed
          $comments = Comment::fetchByFollow($db, $user->getUserId(), 24);
          if ($comments == null) {
              $comments = [];
          }

          foreach (Comment::fetchByUser($db, $user->getUserId()) as $comment) {
              array_push($comments, $comment);
          }

          //print_r($comments);
          $messages = Message::fetchAllRecipient($db, $user->getUserId(), 24);
          if ($messages == null) {
              $messages = [];
          }

          $sent = Message::fetchAllSender($db, $user->getUserId(), 24);
          if ($sent == null) {
              $sent = [];
          }

          // Activity feed array
          $events = array_merge($messages, $comments, $sent);
          usort($events, "\app\models\UserEvent_sorting_key");
      }
        require "app/views/index.phtml";
    }

}
