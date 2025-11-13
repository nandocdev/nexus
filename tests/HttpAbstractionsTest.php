<?php

use PHPUnit\Framework\TestCase;
use Nexus\Modules\Http\Request;
use Nexus\Modules\Http\Response;
use Nexus\Modules\Http\ApiResource;

class HttpAbstractionsTest extends TestCase {
    public function testRequestCapture() {
        // Mock server variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = ['param' => 'value'];

        $request = Request::capture();

        $this->assertEquals('GET', $request->method());
        $this->assertEquals('/test', $request->path());
        $this->assertEquals('value', $request->query('param'));
    }

    public function testResponseJson() {
        $data = ['message' => 'test'];
        $response = Response::json($data, 200);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertContains('application/json', $response->getHeaders()['Content-Type']);
    }

    public function testApiResourceSuccess() {
        $data = ['id' => 1, 'name' => 'test'];
        $response = ApiResource::success($data, 'Operation successful', 201);

        $this->assertEquals(201, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('Operation successful', $content['message']);
        $this->assertEquals($data, $content['data']);
    }

    public function testApiResourceError() {
        $response = ApiResource::error('Something went wrong', 400, ['field' => 'Field is required']);

        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Something went wrong', $content['message']);
        $this->assertEquals(['field' => 'Field is required'], $content['errors']);
    }
}