<?php

namespace App\Controller;

use App\Message\AlertMessage;
use App\Service\DestinataireService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AlerteController extends AbstractController
{

    /**
     * @throws Exception
     */
    #[Route('/alerter', name: 'alerter', methods: ['POST','GET'])]
    public function alerter(
        Request $request,
        DestinataireService $destinataireService,
        MessageBusInterface $bus
    ): JsonResponse {
        $validApiKey = $this->getParameter('app.api_key');;

        $apiKey = $request->headers->get('X-API-KEY');

        if (!$apiKey || !$validApiKey || $apiKey !== $validApiKey) {
            return new JsonResponse(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null || !isset($data['insee'])) {
            return new JsonResponse(['error' => 'Code INSEE requis'], 400);
        }

        $insee = $data['insee'];
        $listDestinataires = $destinataireService->findDestinatairesByInsee($insee);

        if (empty($listDestinataires)) {
            return new JsonResponse(['message' => 'Aucun destinataire trouvé'], 404);
        }

        foreach ($listDestinataires as $destinataire) {
            $bus->dispatch(new AlertMessage($destinataire['telephone'], "Alerte météo pour votre région !"));
        }

        return new JsonResponse(['message' => 'Alertes envoyées en file d’attente'], 200);
    }
}
