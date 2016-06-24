<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Item;
use AppBundle\Entity\Urls;

use Sunra\PhpSimple\HtmlDomParser;

class DefaultController extends Controller {

  /**
   * @Route("/add", name="addpage")
   */
  public function addAction(Request $request) {
//
//    $item = new Item();
//    $item->setIdApi('68735');
//
//    // Création d'une première candidature
//
//    $urls = new Urls();
//    $urls->setName('Megashare.com');
//    $urls->setvideo('<source src="https://r20---sn-aigllnel.googlevideo.com/videoplayback?requiressl=yes&id=9db7e8684df928ce&itag=22&source=webdrive&app=explorer&ip=160.176.224.47&ipbits=32&expire=1466221746&sparams=expire,id,ip,ipbits,itag,mm,mn,ms,mv,nh,pl,requiressl,source&signature=555CF7BC5DFC7EE75D0C3F6487E862DFAED93394.68B1EDF45ECC31B64F151D1D5E47965344003291&key=cms1&pl=21&redirect_counter=1&req_id=a65695f7d371a3ee&cms_redirect=yes&mm=30&mn=sn-aigllnel&ms=nxu&mt=1466211090&mv=m&nh=IgpwZjAxLmxocjI2Kg0xNDkuMTEuMTY3LjQx"  type="video/mp4">');
//    $urls->setQualite('HD');
//    $urls->setItem($item);
//
//        $urls2 = new Urls();
//    $urls2->setName('Megatv.tv');
//    $urls2->setvideo('<source src="http://aae.cdn.vizplay.org/v1/db63426b4d7335888eb1b213545e6f5e.mp4?st=zJrQ6Yx4YgnTnEoP7naNNw&hash=jkAFJJZuxjA6Lb48wVbQGg"  type="video/mp4">');
//    $urls2->setQualite('CAM');
//    $urls2->setItem($item);
//    // Étape 1 : On « persiste » l'entité
//
//    $em = $this->getDoctrine()->getManager();
//    $em->persist($item);
//    $em->persist($urls);
//        $em->persist($urls2);
//
//    $em->flush();
  }

  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request) {
    $client = $this->get('tmdb.client');
    $TopRatedMovies = $client->getMoviesApi()->getPopular(array('page' => 1));
    $TopRatedMovies = $TopRatedMovies['results'];
    $firstMovies = array_slice($TopRatedMovies, 0, 3);
    $movies = array_slice($TopRatedMovies, 3, 4);
    $TopRatedTV = $client->getTvApi()->getPopular(array('page' => 1));
    $TopRatedTV = $TopRatedTV['results'];
    $firstTvs = array_slice($TopRatedTV, 0, 3);
    $tvs = array_slice($TopRatedTV, 3, 4);
    return $this->render('AppBundle:Core:index.html.twig', array(
          'firstMovies' => $firstMovies,
          'firstTvs' => $firstTvs,
          'movies' => $movies,
          'tvs' => $tvs,
    ));
  }
  
}
