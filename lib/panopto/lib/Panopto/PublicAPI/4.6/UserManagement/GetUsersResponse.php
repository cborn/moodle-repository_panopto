<?php

namespace Panopto\UserManagement;

class GetUsersResponse
{

    /**
     * @var ArrayOfUser $GetUsersResult
     */
    protected $GetUsersResult = null;

    /**
     * @param ArrayOfUser $GetUsersResult
     */
    public function __construct($GetUsersResult)
    {
      $this->GetUsersResult = $GetUsersResult;
    }

    /**
     * @return ArrayOfUser
     */
    public function getGetUsersResult()
    {
      return $this->GetUsersResult;
    }

    /**
     * @param ArrayOfUser $GetUsersResult
     * @return \Panopto\UserManagement\GetUsersResponse
     */
    public function setGetUsersResult($GetUsersResult)
    {
      $this->GetUsersResult = $GetUsersResult;
      return $this;
    }

}
