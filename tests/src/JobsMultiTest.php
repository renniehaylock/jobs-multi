<?php namespace JobApis\Jobs\Client\Tests;

use JobApis\Jobs\Client\Collection;
use Mockery as m;
use JobApis\Jobs\Client\JobsMulti;

class JobsMultiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobsMulti
     */
    protected $client;

    public function setUp()
    {
        $this->providers = [
            'Careerbuilder' => [
                'DeveloperKey' => uniqid(),
            ],
            'Careercast' => [],
            'Dice' => [],
            'Github' => [],
            'Govt' => [],
            'Ieee' => [],
            'Indeed' => [
                'publisher' => uniqid(),
            ],
            'Jobinventory' => [],
            'Juju' => [
                'partnerid' => uniqid(),
            ],
            'Usajobs' => [
                'AuthorizationKey' => uniqid(),
            ],
            'Ziprecruiter' => [
                'api_key' => uniqid(),
            ],
        ];
        $this->client = new JobsMulti($this->providers);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testItCannotSetLocationOnProvidersWhenInvalid()
    {
        $location = uniqid().' '.uniqid();
        $this->client->setLocation($location);
    }

    public function testItCannotGetJobsByProviderWhenExceptionThrown()
    {
        $result = $this->client->getJobsByProvider(uniqid());

        $this->assertEquals(Collection::class, get_class($result));
        $this->assertNotNull($result->getErrors());
    }

    public function testItCanGetResultsFromSingleApi()
    {
        if (!getenv('REAL_CALL')) {
            $this->markTestSkipped('REAL_CALL not set. Real API calls will not be made.');
        }

        $keyword = 'engineering';
        $providers = [
            'Dice' => [],
        ];
        $client = new JobsMulti($providers);

        $client->setKeyword($keyword);

        $results = $client->getJobsByProvider('Dice');

        $this->assertInstanceOf('JobApis\Jobs\Client\Collection', $results);
        foreach($results as $job) {
            $this->assertEquals($keyword, $job->query);
        }
    }

    public function testItCanGetAllResultsFromApis()
    {
        if (!getenv('REAL_CALL')) {
            $this->markTestSkipped('REAL_CALL not set. Real API calls will not be made.');
        }

        $providers = [
            'Dice' => [],
            'Github' => [],
            'Govt' => [],
            'Ieee' => [],
            'Jobinventory' => [],
            'Stackoverflow' => [],
        ];
        $client = new JobsMulti($providers);
        $keyword = 'engineering';

        $client->setKeyword($keyword)
            ->setLocation('Chicago, IL')
            ->setPage(1, 10);

        $jobs = $client->getAllJobs();

        foreach ($jobs as $provider => $results) {
            $this->assertInstanceOf('JobApis\Jobs\Client\Collection', $results);
            foreach($results as $job) {
                $this->assertEquals($keyword, $job->query);
            }
        }
    }

    private function getProtectedProperty($object, $property = null)
    {
        $class = new \ReflectionClass(get_class($object));
        $property = $class->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    private function getRandomProvider()
    {
        return array_rand($this->providers);
    }
}
