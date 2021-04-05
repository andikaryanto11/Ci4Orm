<?php

namespace AndikAryanto11\Libraries;

use Exception;
use JsonSerializable;

class EloquentPaging
{

	private $eloquent      = '';
	private $filter        = [];
	private $page          = 1;
	private $size          = 1;
	private $showedPage = null;

	protected $output = [
		'CurrentPage' => null,
		'TotalPage'   => null,
		'Data'        => null,
		'TotalData'   => null,
		'ShowingPage' => [],
		'GetQuery'    => null,

	];

	public function __construct($eloquent, $filter = [], $page = 1, $size = 6, $showedPage = 5, $queryParams = [])
	{
		$this->eloquent              = $eloquent;
		$this->filter                = $filter;
		$this->size                  = $size;
		$this->showedPage = $showedPage;
		$this->output['CurrentPage'] = (int)$page;

		$this->customRequest      = \Config\Services::request();
		$this->output['GetQuery'] = $this->createGetParam($queryParams);

		if (! is_numeric($page))
		{
			$this->page = 1;
		}
		else
		{
			$this->page = $page;
		}
	}

	private function createGetParam($params, $except = '')
	{
		if (! empty($except))
		{
			unset($params[$except]);
		}
	
		$getQuery = http_build_query ($params);
		return $getQuery;
	}
	

	private function setPaging()
	{
		$showedPage = $this->showedPage;
		$expandedPage = round($showedPage / 2, 0, PHP_ROUND_HALF_DOWN);

		$lastPage  = $this->page + $expandedPage;
		$firstPage = $lastPage - $showedPage + 1;

		if ($firstPage <= 0)
		{
			$firstPage = 1;
		}

		if ($this->output['TotalPage'] <= $showedPage)
		{
			$lastPage = $this->output['TotalPage'];
			if ($showedPage - $firstPage !== $showedPage - 1)
			{
				$firstPage = 1;
			}
		}
		else
		{
			if ($this->page < ($lastPage - $expandedPage))
			{
				$lastPage = $showedPage;
				if ($lastPage > $this->output['TotalPage'])
				{
					$lastPage = $this->output['TotalPage'];
				}
			}
			else
			{
				if ($this->page < ($lastPage - $expandedPage) || $lastPage < $showedPage)
				{
					$lastPage = $showedPage;
				}
				if ($this->page >= $this->output['TotalPage'] - $expandedPage)
				{
					$lastPage  = $this->output['TotalPage'];
					$firstPage = $this->output['TotalPage'] - ($expandedPage * 2);
				}
			}
		}

		for ($i = $firstPage; $i <= $lastPage; $i++)
		{
			$this->output['ShowingPage'][] = $i;
		}
	}

	public function setParams()
	{
		$params               = $this->filter;
		$params['limit'] = [
			'page' => $this->page,
			'size' => $this->size,
		];

		return $params;
	}

	public function fetch()
	{
		try
		{
			$params = $this->setParams();
			$result = $this->eloquent::findAll($params);

			$this->output['TotalPage'] = ceil(intval($this->allData($params)) / $this->size);
			$this->output['Data']      = $result;
			$this->output['TotalData'] = $this->allData($params);
			$this->setPaging();
		}
		catch (Exception $e)
		{
			$this->output['Error'] = $e->getMessage();
		}

		return (object)$this->output;
	}

	private function allData($filter = [])
	{
		$params = [
			'join'       => isset($filter['join']) ? $filter['join'] : null,
			'where'      => isset($filter['where']) ? $filter['where'] : null,
			'whereIn'    => isset($filter['whereIn']) ? $filter['whereIn'] : null,
			'orWhere'    => isset($filter['orWhere']) ? $filter['orWhere'] : null,
			'whereNotIn' => isset($filter['whereNotIn']) ? $filter['whereNotIn'] : null,
			'like'       => isset($filter['like']) ? $filter['like'] : null,
			'orLike'     => isset($filter['orLike']) ? $filter['orLike'] : null,
			'group'      => isset($filter['group']) ? $filter['group'] : null,
		];
		
		return $this->eloquent::count($filter);
	}
}
