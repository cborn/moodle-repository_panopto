<?php

namespace Panopto\SessionManagement;

class GetNoteResponse
{

    /**
     * @var Note $GetNoteResult
     */
    protected $GetNoteResult = null;

    /**
     * @param Note $GetNoteResult
     */
    public function __construct($GetNoteResult)
    {
      $this->GetNoteResult = $GetNoteResult;
    }

    /**
     * @return Note
     */
    public function getGetNoteResult()
    {
      return $this->GetNoteResult;
    }

    /**
     * @param Note $GetNoteResult
     * @return \Panopto\SessionManagement\GetNoteResponse
     */
    public function setGetNoteResult($GetNoteResult)
    {
      $this->GetNoteResult = $GetNoteResult;
      return $this;
    }

}
