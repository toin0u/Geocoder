<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpInfoDb;

class IpInfoDbTest extends TestCase
{
    public function testGetName()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('ip_info_db', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGetDataWithNullApiKey()
    {
        $provider = new IpInfoDb($this->getMock('\Ivory\HttpAdapter\HttpAdapterInterface'), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithRandomString()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getLatitude());
        $this->assertNull($result->getLongitude());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());

        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCounty()->getName());
        $this->assertEquals('localhost', $result->getRegion()->getName());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support IPv6 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.ipinfodb.com/v3/ip-city/?key=api_key&format=json&ip=74.125.45.100".
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new IpInfoDb($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('74.125.45.100');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.ipinfodb.com/v3/ip-city/?key=api_key&format=json&ip=74.125.45.100".
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new IpInfoDb($this->getMockAdapterReturns(''), 'api_key');
        $provider->geocode('74.125.45.100');
    }

    public function testGeocodeWithRealIPv4()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getAdapter(), $_SERVER['IPINFODB_API_KEY']);
        $results  = $provider->geocode('74.125.45.100');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(37.406, $result->getLatitude(), '', 0.001);
        $this->assertEquals(-122.079, $result->getLongitude(), '', 0.001);
        $this->assertEquals(94043, $result->getPostalCode());
        $this->assertEquals('MOUNTAIN VIEW', $result->getLocality());
        $this->assertEquals('CALIFORNIA', $result->getRegion()->getName());
        $this->assertEquals('UNITED STATES', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/Denver', $result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support IPv6 addresses, only IPv4 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getAdapter(), $_SERVER['IPINFODB_API_KEY']);
        $provider->geocode('::ffff:74.125.45.100');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new IpInfoDb($this->getMock('\Ivory\HttpAdapter\HttpAdapterInterface'), 'api_key');
        $provider->reverse(null, null);
    }
}
