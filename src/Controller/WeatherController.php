<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WeatherService;
use App\Entity\OpenWeatherMapForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class WeatherController extends AbstractController
{
  private $weatherService;

  public function __construct(WeatherService $weather)
  {
    $this->weatherService = $weather;
  }

  /**
   * @Route("/weather", name="weather")
   */
  public function index(Request $request)
  {
    //Creation FORM
    
    $city_name = new OpenWeatherMapForm();

    $form = $this->createFormBuilder($city_name)
      ->add('city_name', TextType::class)
      ->add('save', SubmitType::class, ['label' => 'Search'])
      ->getForm();
    
    $form->handleRequest($request);

    //VÉRIFICATION

    if ($form->isSubmitted() && $form->isValid()) {
      $city_name = $form->getData();

      return $this->redirectToRoute('weather_city', [
        'city' => $city_name->getCityName(),
      ]);
    }
    return $this->render('weather/index.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * @Route("/weather/{city}", name="weather_city")
   */
  public function number($city)
  {
    
    //RÉPONSE
    $data = $this->weatherService->getWeather($city);
    if (is_array($data)) {
      return $this->render('weather/result.html.twig', ['data' => $data]);
    } else {
      $statusCode = 0;
      $errorMessage = '';
      $e = $data;
      if (method_exists($e, 'getResponse')) {
        $statusCode = $e->getResponse()->getStatusCode();
      }
      if ($statusCode == 0) {
        $errorMessage = 'Statue Error occurs';
      }
      if (404 == $statusCode) {
        $errorMessage = "S'il te plait recherche une ville qui existe 😩";
      }
      
      return $this->render('errors.html.twig', ['error' => $errorMessage]);
    }
  }

  
  
}
