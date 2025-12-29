<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model\Country;
use App\Model\CountryScenarios;
use App\Model\Exceptions\CountryNotFoundException;
use App\Model\Exceptions\CountryValidationException;
use App\Model\Exceptions\CountryDuplicateException;

#[Route('api/country', name: 'app_api_country')]
final class CountryController extends AbstractController
{
    public function __construct(
        private readonly CountryScenarios $countries,
    ) {}

    #[Route('', name: 'app_api_country_get_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $countries = $this->countries->getAll();
        
         $countriesArray = array_map(function($country) {
            return [
                'shortName' => $country->shortName,
                'fullName' => $country->fullName,
                'isoAlpha2' => $country->isoAlpha2,
                'isoAlpha3' => $country->isoAlpha3,
                'isoNumeric' => $country->isoNumeric,
                'population' => $country->population,
            ];
        }, $countries);
        
        return $this->json($countriesArray, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_api_country_get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $country = $this->countries->get($id);
            
            // Полная сериализация объекта страны
            $countryData = [
                'shortName' => $country->shortName,
                'fullName' => $country->fullName,
                'isoAlpha2' => $country->isoAlpha2,
                'isoAlpha3' => $country->isoAlpha3,
                'isoNumeric' => $country->isoNumeric,
                'population' => $country->population,
            ];
            
            return $this->json($countryData, Response::HTTP_OK);
            
        } catch (CountryValidationException $e) {
            // 400 - невалидный код
            return $this->json(['error' => $e->getMessage()],Response::HTTP_BAD_REQUEST);
        } catch (CountryNotFoundException $e) {
            // 404 - валидный код, но страна не найдена
            return $this->json(['error' => $e->getMessage()],Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'app_api_country_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
        
            // Проверяем наличие всех обязательных полей
            $requiredFields = ['shortName', 'fullName', 'isoAlpha2', 'isoAlpha3', 'isoNumeric', 'population'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    return $this->json(
                        ['error' => "Field '$field' is required"], 
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }
            
            $data['isoNumeric'] = (int)$data['isoNumeric'];
            $data['population'] = (int)$data['population'];

            // Создаем объект Country
            $country = new Country(
                $data['shortName'],
                $data['fullName'],
                $data['isoAlpha2'],
                $data['isoAlpha3'],
                $data['isoNumeric'],
                $data['population']
            );
        
            // Вызываем метод store в CountryScenarios
            $this->countries->store($country);
        
            // Возвращаем 204 No Content
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        
        } catch (CountryValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (CountryDuplicateException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }


    #[Route('/{code}', name: 'app_api_country_edit', methods: ['PATCH'])]
    public function edit(string $code, Request $request): JsonResponse
    {
        try {
            // Получаем входные данные и валидируем наличие нужных полей
            $data = json_decode($request->getContent(), true);
            $updateData = new CountryUpdateData($data);

            // Получаем текущую страну (а значит валидируем код и существование)
            $country = $this->countries->get($code);

            // Коды не могут меняться, в остальном применяем обновления
            $updated = new Country(
                $updateData->shortName,
                $updateData->fullName,
                $country->isoAlpha2,
                $country->isoAlpha3,
                $country->isoNumeric,
                $updateData->population
            );

            // Пытаемся обновить
            $this->countries->edit($code, $updated);

            // Возвращаем полностью обновлённый объект
            $result = [
                'shortName' => $updated->shortName,
                'fullName' => $updated->fullName,
                'isoAlpha2' => $updated->isoAlpha2,
                'isoAlpha3' => $updated->isoAlpha3,
                'isoNumeric' => $updated->isoNumeric,
                'population' => $updated->population,
            ];
            return $this->json($result, Response::HTTP_OK);
        } catch (CountryValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (CountryDuplicateException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (CountryNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{code}', name: 'app_api_country_delete', methods: ['DELETE'])]
    public function delete(string $code): JsonResponse
    {
        try {
            $this->countries->delete($code);
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (CountryValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (CountryNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
