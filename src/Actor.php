<?php


namespace EasySwoole\Actor;


use EasySwoole\Actor\Exception\InvalidActor;
use EasySwoole\Component\Singleton;

class Actor
{
    private $actorList = [];

    use Singleton;

    /**
     * @param string $actorClass
     * @throws InvalidActor
     */
    public function register(string $actorClass)
    {
        try{
            $ref = new \ReflectionClass($actorClass);
            if($ref->isSubclassOf(AbstractActor::class)){
                $config = new ActorConfig();
                $actorClass::configure($config);
                if(empty($config->getActorName())){
                    throw new InvalidActor("actor name for class:{$actorClass} is required");
                }
                if(in_array($config->getActorName(),$this->actorList)){
                    throw new InvalidActor("actor name for class:{$actorClass} is duplicate");
                }
                $this->actorList[$actorClass] = $config;
            }else{
                throw new InvalidActor("{$actorClass} is not an sub class of ".AbstractActor::class);
            }
        }catch (\Throwable $throwable){
            throw new InvalidActor($throwable->getMessage());
        }
    }
}