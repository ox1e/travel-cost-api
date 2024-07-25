<?php

namespace App\Controller;

use App\DTO\TravelCostRequest;
use App\Contract\TravelCostCalculatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

class TravelCostCalculatorController extends AbstractController
{
    private TravelCostCalculatorInterface $calculator;
    private ValidatorInterface $validator;

    public function __construct(TravelCostCalculatorInterface $calculator, ValidatorInterface $validator)
    {
        $this->calculator = $calculator;
        $this->validator = $validator;
    }

    #[Route('/calculate', name: 'calculate_travel_cost', methods: ['POST'])]
    /**
     * @OA\Post(
     *     path="/calculate",
     *     summary="Calculate travel cost",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"baseCost", "startDate", "birthDate", "paymentDate"},
     *             @OA\Property(property="baseCost", type="number", example=10000),
     *             @OA\Property(property="startDate", type="string", format="date", example="2027-05-01"),
     *             @OA\Property(property="birthDate", type="string", format="date", example="2020-01-01"),
     *             @OA\Property(property="paymentDate", type="string", format="date", example="2026-11-01")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Calculated cost", @OA\JsonContent(@OA\Property(property="finalCost", type="number", example=4500))),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function calculate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($this->isInvalidJson($data)) {
            return $this->createErrorResponse('Invalid JSON', Response::HTTP_BAD_REQUEST);
        }

        $missingFields = $this->getMissingFields($data);
        if (!empty($missingFields)) {
            return $this->createErrorResponse(['errors' => $missingFields], Response::HTTP_BAD_REQUEST);
        }

        $travelCostRequest = $this->createTravelCostRequest($data);
        if ($travelCostRequest === null) {
            return $this->createErrorResponse('Invalid request data', Response::HTTP_BAD_REQUEST);
        }

        $validationErrors = $this->validateRequest($travelCostRequest);
        if (!empty($validationErrors)) {
            return $this->createErrorResponse(['errors' => $validationErrors], Response::HTTP_BAD_REQUEST);
        }

        return $this->calculateFinalCost($travelCostRequest);
    }

    private function isInvalidJson($data): bool
    {
        return $data === null;
    }

    private function getMissingFields(array $data): array
    {
        $requiredFields = ['baseCost', 'startDate', 'birthDate', 'paymentDate'];
        return array_filter($requiredFields, fn($field) => !isset($data[$field]));
    }

    private function createTravelCostRequest(array $data): ?TravelCostRequest
    {
        try {
            return new TravelCostRequest(
                $data['baseCost'],
                $data['startDate'],
                $data['birthDate'],
                $data['paymentDate']
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    private function validateRequest(TravelCostRequest $request): array
    {
        $violations = $this->validator->validate($request);
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return $errors;
    }

    private function calculateFinalCost(TravelCostRequest $request): JsonResponse
    {
        try {
            $finalCost = $this->calculator->calculate($request);
            return new JsonResponse(['finalCost' => $finalCost]);
        } catch (\InvalidArgumentException $e) {
            return $this->createErrorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->createErrorResponse('An unexpected error occurred.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createErrorResponse($message, int $status): JsonResponse
    {
        return new JsonResponse(is_array($message) ? $message : ['error' => $message], $status);
    }
}
