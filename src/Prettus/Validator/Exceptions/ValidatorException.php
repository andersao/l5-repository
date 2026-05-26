<?php namespace Prettus\Validator\Exceptions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\MessageBag;

/**
 * Class ValidatorException
 * @package Prettus\Validator\Exceptions
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class ValidatorException extends \Exception implements Jsonable, Arrayable
{
    /**
     * @var MessageBag
     */
    protected $messageBag;

    /**
     * @param MessageBag $messageBag
     */
    public function __construct(MessageBag $messageBag)
    {
        $this->messageBag = $messageBag;
    }

    /**
     * @return MessageBag
     */
    public function getMessageBag()
    {
        return $this->messageBag;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'error'=>'validation_exception',
            'error_description'=>$this->getMessageBag()
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
