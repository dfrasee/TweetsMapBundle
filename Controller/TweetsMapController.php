<?php

namespace TweetsMapBundle\Controller;
/**
 * An action control event for search city.
 *
 * @package   Tweets
 * @version   1.0.0-dev
 * @author    Dangphan Rasee <rasee59@gmail.com>
 * @copyright 2014 Dangphan Rasee <rasee59@gmail.com>
 * @license   http://opensource.org/licenses/GPL-3.0 GNU General Public License 3.0
 * @link      https://github.com/dfrasee/TweetsMapBundle
 */
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\Common\Cache\FilesystemCache;
use TweetsMapBundle\Entity\History;
use TweetsMapBundle\Services\Twitter;

/**
 * @Route("/tweets")
 */
class TweetsMapController extends Controller
{
    /**
     * @Route("/", name="landingpage")
     * @Template("TweetsMapBundle:Tweets:landingpage.html.twig")
     */
    public function indexAction()
    {
        return array('title'=>'Search Tweets', 'data'=>array('status'=>0));
    }
    
     /**
     * Retrives histories data of current user sort by recent search
     * 
     * @Route("/history", name="search_history")
     * @Template("TweetsMapBundle:Tweets:histories.html.twig")
     */
    public function historyAction()
    {
        // valid user from cookie
        $user = '';
        $list = array();
        $request = $this->get('request');
        $cookies = $request->cookies;
        if ($cookies->has('searh_user')){
            $user = $cookies->get('searh_user');
            // valid user data from database 
            $em = $this->getDoctrine()->getEntityManager();
            $histories = $em->getRepository('TweetsMapBundle:History')->findBy(array('user'=>$user),array('last_search' => 'DESC'));
            foreach($histories as $item){
                if($item->getKeySearch()){
                    $his['id']      = $item->getId();
                    $his['city']    = $item->getKeySearch();
                    $his['count']   = $item->getCount();
                    $his['lat']     = $item->getLat();
                    $his['lng']     = $item->getLng();
                    $his['lastsearch']     = $item->getLastSearch();
                    $list[]         = $his;
                }
            }
        }
       
        return array('title'=>'Your Histories Search','list' => $list,'user'=>$user);
    }
    
    /**
     * Do seach tweets from specify key (city)
     * 
     * @Route("/search/{city}/{lat}/{lng}", name="search")
     * @Template("TweetsMapBundle:Tweets:landingpage.html.twig")
     */
    public function searchAction($city='',$lat=0,$lng=0)
    {
        $data['status'] = 0;
        if($city){
            $data = $this->_search_proceed($city,$lat,$lng);
        }
        return array('title'=>'Search Tweets','city'=>$city, 'data'=>$data);
    }
    
    /**
     * Do seach tweets from specify key (city) by ajax request
     *  
     * @Route("/ajax_search", name="ajax_search")
     * @param Request $request the request object
     */
    public function ajaxSearchAction(Request $r)
    {
        $city = $r->get('q');
        $data['status'] = 0;
        if($city){
            $data = $this->_search_proceed($city);
        }
        echo json_encode($data);
        exit;
    }
    
    
    /**
     * Do search process, store cache result for each key have 1 hour lifetime 
     * 
     * @param String $city
     * @param Numeric $lat
     * @param Numeric $lng  
     */
    private function _search_proceed($city,$lat=0,$lng=0){
        
        $data['city'] = $city;
        $data['lat']    = $lat;
        $data['lng']    = $lng;
        /**
         * @TODO change to APC, Memcache to be more powerfull of retrive data
         * but for those provider the server need to compiled and enabled in php.ini.
         */
        $kernel = $this->get('kernel'); 
        $cacheDriver = new FilesystemCache($kernel->getCacheDir());
        
        // retrive data from the cache when specify key search already existed in tweets cache data and not expired yet.
        $tweets_cache_data = $cacheDriver->fetch('tweets_'.$data['city']);
        if ($tweets_cache_data)
        {
            $data = unserialize($tweets_cache_data);
            $data['status'] = 1;
        } 
        else 
        {
            if(!$lat && !$lng) {  
                //Search location to get lat and lng value
                $google_api_key = $this->container->getParameter('google_api_key');
                $loc_info = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($data['city']).'&key='.$google_api_key);
                $geo = json_decode($loc_info);

                // retrive geolocation
                if($geo)
                {
                    if(isset($geo->results[0]))
                    {
                        $data['lat']    = $geo->results[0]->geometry->location->lat;
                        $data['lng']    = $geo->results[0]->geometry->location->lng;
                    }
                }
            }
            if($data['city'] && $data['lat'] && $data['lng']) {
                //Search tweets that metion about search key within 50km.
                $oauth_access_token         = $this->container->getParameter('twitter_oauth_access_token');
                $oauth_access_token_secret  = $this->container->getParameter('twitter_oauth_access_token_secret');
                $consumer_key               = $this->container->getParameter('twitter_consumer_key');
                $consumer_secret            = $this->container->getParameter('twitter_consumer_secret');

                $api = new Twitter();
                $api->setToken($oauth_access_token, $oauth_access_token_secret);
                $api->setConsumerKey($consumer_key, $consumer_secret);
                $result_tweets = $api->search_tweets('q='.urlencode($data['city']).'&geocode='.urlencode($data['lat']).','.urlencode($data['lng']).',50km', true);

                // provide only required data;
                $data['tweets'] = array();
                if($result_tweets)
                {
                    foreach($result_tweets->statuses as $tweets)
                    {
                        $list['avatar']     = $tweets->user->profile_image_url;
                        $list['title']      = $tweets->text;
                        $coors              = $tweets->geo->coordinates;
                        $list['lat']        = $coors[0];
                        $list['lng']        = $coors[1];
                        $data['tweets'][]   = $list;
                    }
                }

                //Store result as cache lifetime is 1 hour
                $cacheDriver->save('tweets_'.$data['city'], serialize($data), 3600);
                $data['status'] = 1;
           }
            
        }
        
        // update histories on database 
        $this->_update_history($data);
        return $data;
    }
    
    /**
     * Do update database for search histories data
     * 
     * @param array $data information of search (city,lat,lng)
     */
    private function _update_history($data){
         // valid user from cookie
        $request = $this->get('request');
        $cookies = $request->cookies;
        
        // if not exist then create a uniq user
        if (!$cookies->has('searh_user'))
        {
            //set life time to 1 years 
            $user = uniqid();
            $response = new Response();
            $response->headers->setCookie(new Cookie('searh_user', $user, (time() + 3600 * 24 * 365 * 5), '/'));
            $response->send();  
        }else{
            $user = $cookies->get('searh_user');
        }
        
        // Store data to database 
        if($user){
            // valid user data from database 
            $em = $this->getDoctrine()->getManager();
            $history = $em->getRepository('TweetsMapBundle:History')->findOneBy(array('user'=>$user,'key_search'=>$data['city']));
            // create new record if not exists
            if(!$history){
                $history = new History();
                $history->setUser($user);
                $history->setKeySearch($data['city']);
                $history->setLat($data['lat']);
                $history->setLng($data['lng']);
                $history->setCount(1);
            }else{
                // update count if exists
                $history->setCount(($history->getCount()+1));
            }
            $em->persist($history);
            $em->flush();
        }
    }
 
}
