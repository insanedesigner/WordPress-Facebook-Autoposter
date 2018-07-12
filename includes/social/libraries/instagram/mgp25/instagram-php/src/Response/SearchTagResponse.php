<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getHasMore()
 * @method mixed getRankToken()
 * @method Model\Tag[] getResults()
 * @method bool isHasMore()
 * @method bool isRankToken()
 * @method bool isResults()
 * @method setHasMore(mixed $value)
 * @method setRankToken(mixed $value)
 * @method setResults(Model\Tag[] $value)
 */
class SearchTagResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $has_more;
    /**
     * @var Model\Tag[]
     */
    public $results;
    public $rank_token;
}
