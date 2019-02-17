<?php

namespace App\Controllers;

use App\Validation\Validator;
use Illuminate\Database\Query\Builder;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Respect\Validation\Validator as v;

/**
 * Class ApiController
 * @package App\Controllers
 */
class ApiController
{
	/** @var Builder */
	private $table;

	/** @var Validator */
	private $validator;

	/** @var Response */
	private $response;

	/** @var array */
	private $fieldsMapping = [];


	/**
	 * ApiController constructor.
	 * @param Builder $table
	 */
	public function __construct(Builder $table)
	{
		$this->table = $table;
		$this->validator = new Validator();

		$this->fieldsMapping = [
			'title' => [v::notEmpty()],
			'position' => [v::notEmpty()],
			'status' => [v::notEmpty()]
		];
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $arguments
	 * @return Response
	 */
	public function read(Request $request, Response $response, $arguments)
	{
		try {

			if ($arguments) {

				/**
				 * Fetch One
				 */
				if (empty($arguments['id'])) {
					throw new \Exception('Required argument does not exists: id', StatusCode::HTTP_BAD_REQUEST);
				}

				$data = $this->table->find($arguments['id']);
				if (!$data) throw new \Exception('Entry not found', StatusCode::HTTP_NOT_FOUND);

			} else {

				/**
				 * Fetch All
				 */
				$data = $this->table->get()->toArray();
			}

			$this->response = $response->withJson([
				'status' => true,
				'data' => $data
			]);

		} catch (\Exception $e) {
			$this->response = $response->withJson([
				'status' => false,
				'message' => $e->getMessage()
			], $e->getCode() ?: StatusCode::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $this->response;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function create(Request $request, Response $response)
	{
		try {

			/**
			 * Validation
			 */
			$validation = $this->validator->validate($request, $this->fieldsMapping);
			if ($validation->isFailed()) throw new \Exception($validation->getErrorsAsString());

			/**
			 * Read only mapped fields
			 */
			$insertFields = [];
			foreach (array_keys($this->fieldsMapping) as $field) {
				$insertFields[$field] = $request->getParsedBody()[$field];
			}

			/**
			 * Inserting & Getting ID
			 */
			$scriptId = $this->table->insertGetId($insertFields);
			if (!$scriptId) throw new \Exception('Something went wrong', StatusCode::HTTP_INTERNAL_SERVER_ERROR);

			/**
			 * Fetch New Data
			 */
			$data = $this->table->find($scriptId);
			if (!$data) throw new \Exception('Entry not found', StatusCode::HTTP_NOT_FOUND);

			$this->response = $response->withJson([
				'status' => true,
				'data' => $data,
			], StatusCode::HTTP_CREATED);

			return $this->response;

		} catch (\Exception $e) {
			$this->response = $response->withJson([
				'status' => false,
				'message' => $e->getMessage()
			], $e->getCode() ?: StatusCode::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $this->response;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param $arguments
	 * @return Response
	 */
	public function update(Request $request, Response $response, $arguments)
	{
		try {

			if (empty($arguments['id'])) {
				throw new \Exception('Required argument doesnt exists: id', StatusCode::HTTP_BAD_REQUEST);
			}

			/**
			 * Check Exists
			 */
			$data = $this->table->find($arguments['id']);
			if (!$data) throw new \Exception('Entry not found', StatusCode::HTTP_NOT_FOUND);

			/**
			 * Validation
			 */
			$validation = $this->validator->validate($request, $this->fieldsMapping);
			if ($validation->isFailed()) throw new \Exception($validation->getErrorsAsString());

			/**
			 * Read only mapped fields
			 */
			$updateFields = [];
			foreach (array_keys($this->fieldsMapping) as $field) {
				$updateFields[$field] = $request->getParsedBody()[$field];
			}

			/**
			 * Updating
			 */
			$status = $this->table->where('id', $arguments['id'])->update($updateFields);
			if (!$status) throw new \Exception('Entry already updated', StatusCode::HTTP_CONFLICT);

			/**
			 * Fetch Updated Data
			 */
			$data = $this->table->find($arguments['id']);
			if (!$data) throw new \Exception('Entry not found', StatusCode::HTTP_NOT_FOUND);

			$this->response = $response->withJson([
				'status' => true,
				'data' => $data,
			]);

			return $this->response;

		} catch (\Exception $e) {
			$this->response = $response->withJson([
				'status' => false,
				'message' => $e->getMessage()
			], $e->getCode() ?: StatusCode::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $this->response;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $arguments
	 * @return Response
	 */
	public function delete(Request $request, Response $response, $arguments)
	{
		try {

			if (empty($arguments['id'])) {
				throw new \Exception('Required argument does not exists: id', StatusCode::HTTP_BAD_REQUEST);
			}

			/**
			 * Check Exists
			 */
			$data = $this->table->find($arguments['id']);
			if (!$data) throw new \Exception('Entry not found', StatusCode::HTTP_NOT_FOUND);

			/**
			 * Deletion
			 */
			$status = $this->table->delete($arguments['id']);
			if (!$status) throw new \Exception('Something went wrong', StatusCode::HTTP_INTERNAL_SERVER_ERROR);

			$this->response = $response->withJson([
				'status' => true,
			]);

		} catch (\Exception $e) {
			$this->response = $response->withJson([
				'status' => false,
				'message' => $e->getMessage()
			], $e->getCode() ?: StatusCode::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $this->response;
	}

}