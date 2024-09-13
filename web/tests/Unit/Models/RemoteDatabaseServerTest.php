<?php

namespace tests\Unit\Models;

use App\Models\RemoteDatabaseServer;
use Doctrine\DBAL\DriverManager;
use Illuminate\Foundation\Testing\TestCase;

class RemoteDatabaseServerTest extends TestCase
{
//    public function testCreateRemoteDatabaseServer() {
//        $testName = 'test' . uniqid();
//        $testHost = 'alma-4gb-hel1-1';
//        $testPort = '3306';
//        $testDatabaseType = 'mysql';
//        $testUsername = 'test' . uniqid();
//        $testPassword = 'test' . uniqid();
//
//        $testCreateRemoteDatabaseServer = new RemoteDatabaseServer();
//        $testCreateRemoteDatabaseServer->name = $testName;
//        $testCreateRemoteDatabaseServer->host = $testHost;
//        $testCreateRemoteDatabaseServer->port = $testPort;
//        $testCreateRemoteDatabaseServer->database_type = $testDatabaseType;
//        $testCreateRemoteDatabaseServer->username = $testUsername;
//        $testCreateRemoteDatabaseServer->password = $testPassword;
//        $testCreateRemoteDatabaseServer->save();
//
//        $this->assertIsObject($testCreateRemoteDatabaseServer);
//        $this->assertDatabaseHas('create_remote_database_servers', [
//            'id' => $testCreateRemoteDatabaseServer->id
//        ]);
//
//        $this->assertTrue($testCreateRemoteDatabaseServer->status === 'online');
//    }

//    public function testHealthCheck() {
//        $testName = 'test' . uniqid();
//        $testHost = 'alma-4gb-hel1-1';
//        $testPort = '3306';
//        $testDatabaseType = 'mysql';
//        $testUsername = 'test' . uniqid();
//        $testPassword = 'test' . uniqid();
//
//        $testParams = [
//            'user' => $testUsername,
//            'password' => $testPassword,
//            'host' => $testHost,
//            'port' => $testPort,
//            'driver' => 'pdo_mysql'
//        ];
//
//        $testConnection = DriverManager::getConnection($testParams);
//        $this->assertIsObject($testConnection);
//        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $testConnection);
//        $this->assertEquals($testParams, $testConnection->getParams());
//        $testConnection->connect();
//
//        $this->assertTrue($testConnection->isConnected());
//    }
}
