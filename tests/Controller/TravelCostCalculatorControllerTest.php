<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TravelCostCalculatorControllerTest extends WebTestCase
{
    public function testCalculate()
    {
        $client = static::createClient();

        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'baseCost' => 10000,
            'startDate' => '2027-05-01',
            'birthDate' => '2020-01-01',
            'paymentDate' => '2026-11-01'
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('finalCost', $response);

        $this->assertEquals(6510, $response['finalCost']); // Изменить на правильное значение
    }

    public function testCalculateMissingFields()
    {
        $client = static::createClient();

        // Отсутствует поле baseCost
        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'startDate' => '2027-05-01',
            'birthDate' => '2020-01-01',
            'paymentDate' => '2026-11-01'
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertContains('baseCost', $response['errors']);
    }

    public function testCalculateInvalidDateFormat()
    {
        $client = static::createClient();

        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'baseCost' => 10000,
            'startDate' => 'invalid-date',
            'birthDate' => '2020-01-01',
            'paymentDate' => '2026-11-01'
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testCalculateWithExtremeValues()
    {
        $client = static::createClient();

        // Тест с нулевым значением baseCost
        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'baseCost' => 0,
            'startDate' => '2027-05-01',
            'birthDate' => '2020-01-01',
            'paymentDate' => '2026-11-01'
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);

        // Тест с очень большим значением baseCost
        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'baseCost' => 1000000000,
            'startDate' => '2027-05-01',
            'birthDate' => '2020-01-01',
            'paymentDate' => '2026-11-01'
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('finalCost', $response);
    }

    public function testCalculateWithException()
    {
        $client = static::createClient();

        // Предположим, что калькулятор выбрасывает исключение при определенных данных
        $client->request('POST', '/calculate', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'baseCost' => 10000,
            'startDate' => '2027-05-01',
            'birthDate' => 'invalid-date',
            'paymentDate' => '2026-11-01'
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(isset($response['error']) || isset($response['errors']));
    }

}

