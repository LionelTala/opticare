<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Récupérer les notifications de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $limit = $request->query('limit', 50);
            
            $result = $this->notificationService->getUserNotifications($userId, $limit);

            return $this->successResponse([
                'notifications' => NotificationResource::collection($result['notifications']),
                'non_lu_count' => $result['non_lu_count'],
            ], 'Notifications récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des notifications',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $this->notificationService->markAsRead($id, $userId);

            return $this->successResponse(null, 'Notification marquée comme lue.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors du marquage de la notification',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $this->notificationService->markAllAsRead($userId);

            return $this->successResponse(null, 'Toutes les notifications ont été marquées comme lues.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors du marquage des notifications',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Compter les notifications non lues
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $result = $this->notificationService->getUserNotifications($userId, 1);

            return $this->successResponse([
                'count' => $result['non_lu_count'],
            ], 'Nombre de notifications non lues récupéré.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors du comptage des notifications',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}