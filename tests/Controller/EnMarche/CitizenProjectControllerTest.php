<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class CitizenProjectControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAjaxSearchCommittee()
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame(\GuzzleHttp\json_encode([[
            'uuid' => LoadAdherentData::COMMITTEE_1_UUID,
            'name' => 'En Marche Paris 8',
        ]]), $this->client->getResponse()->getContent());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadCitizenProjectData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
