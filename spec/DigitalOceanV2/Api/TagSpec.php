<?php

namespace spec\DigitalOceanV2\Api;

use DigitalOceanV2\Adapter\AdapterInterface;
use DigitalOceanV2\Exception\HttpException;

class TagSpec extends \PhpSpec\ObjectBehavior
{

    function let(AdapterInterface $adapter)
    {
        $this->beConstructedWith($adapter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('DigitalOceanV2\Api\Tag');
    }


    function it_returns_an_empty_array(AdapterInterface $adapter)
    {
        $adapter->get('https://api.digitalocean.com/v2/tags')->willReturn('{"tags": []}');

        $tags = $this->getAll();
        $tags->shouldBeArray();
        $tags->shouldHaveCount(0);
    }


    function it_returns_an_array_of_tag_entity(AdapterInterface $adapter)
    {
        $adapter->get('https://api.digitalocean.com/v2/tags')
            ->willReturn('{"tags": [{},{},{}]}');

        $tags = $this->getAll();
        $tags->shouldBeArray();
        $tags->shouldHaveCount(3);

        foreach ($tags as $tag) {
            /**
             * @var \DigitalOceanV2\Entity\Tag|\PhpSpec\Wrapper\Subject $tag
             */
            $tag->shouldReturnAnInstanceOf('DigitalOceanV2\Entity\Tag');
        }
    }


    function it_returns_a_tag_entity_get_by_its_name(AdapterInterface $adapter)
    {
        $adapter
            ->get('https://api.digitalocean.com/v2/tags/awesome')
            ->willReturn('
                {
                    "tag": {
                        "name": "extra-awesome",
                        "resources": {
                            "count": 3,
                            "last_tagged_uri": "https://api.digitalocean.com/v2/images/7555620",
                            "droplets": {
                                "count": 1,
                                "last_tagged_uri": "https://api.digitalocean.com/v2/droplets/3164444"
                            },
                            "images": {
                                "count": 1,
                                "last_tagged_uri": "https://api.digitalocean.com/v2/images/7555620"
                            },
                            "volumes": {
                                "count": 1,
                                "last_tagged_uri": "https://api.digitalocean.com/v2/volumes/3d80cb72-342b-4aaa-b92e-4e4abb24a933"
                            }
                        }
                    }
                }
            ');

        $this->getByName('awesome')->shouldReturnAnInstanceOf('DigitalOceanV2\Entity\Tag');
    }


    function it_returns_the_created_tag(AdapterInterface $adapter)
    {
        $adapter
            ->post(
                'https://api.digitalocean.com/v2/tags',
                ['name' => 'awesome']
            )
            ->willReturn('
                {
                    "tag": {
                        "name": "awesome",
                        "resources": {
                            "count": 0,
                            "droplets": {
                                "count": 0
                            },
                            "images": {
                                "count": 0
                            },
                            "volumes": {
                                "count": 0
                            }
                        }
                    }
                }
            ');

        $this->create('awesome')->shouldReturnAnInstanceOf('DigitalOceanV2\Entity\Tag');
    }


    function it_tag_resources_and_returns_nothing(AdapterInterface $adapter)
    {
        $resources = [
            [
                "resource_id" =>  "9569411",
                "resource_type" => "droplet",
            ],
            [
                "resource_id" =>  "7555620",
                "resource_type" => "image",
            ],
            [
                "resource_id" =>  "3d80cb72-342b-4aaa-b92e-4e4abb24a933",
                "resource_type" => "volume",
            ]
        ];

        $adapter
            ->post(
                'https://api.digitalocean.com/v2/tags/awesome/resources',
                ['resources' => $resources]
            )
            ->shouldBeCalled();

        $this->tagResources('awesome', $resources);
    }


    function it_untag_resources_and_returns_nothing(AdapterInterface $adapter)
    {
        $resources = [
            [
                "resource_id" => "9569411",
                "resource_type" => "droplet",
            ],
            [
                "resource_id" => "7555620",
                "resource_type" => "image",
            ],
            [
                "resource_id" => "3d80cb72-342b-4aaa-b92e-4e4abb24a933",
                "resource_type" => "volume",
            ]
        ];

        $adapter
            ->delete(
                'https://api.digitalocean.com/v2/tags/awesome/resources',
                ['resources' => $resources]
            )
            ->shouldBeCalled();

        $this->untagResources('awesome', $resources);
    }


    function it_deletes_the_tag_and_returns_nothing(AdapterInterface $adapter)
    {
        $adapter
            ->delete('https://api.digitalocean.com/v2/tags/awesome')
            ->shouldBeCalled();

        $this->delete('awesome');
    }


    function it_throws_an_http_exception_when_trying_to_delete_an_inexisting_tag(AdapterInterface $adapter)
    {
        $adapter
            ->delete('https://api.digitalocean.com/v2/tags/fake')
            ->willThrow(new HttpException('Request not processed.'));

        $this->shouldThrow(new HttpException('Request not processed.'))->during('delete', ['fake']);
    }
}