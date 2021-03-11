<?php

namespace Blomstra\Gdpr\Notifications;

use Blomstra\Gdpr\Models\ErasureRequest;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Notification\MailableInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ErasureRequestCancelledBlueprint implements BlueprintInterface, MailableInterface
{
/**
     * @var ErasureRequest
     */
    private $request;

    public function __construct(ErasureRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function getFromUser()
    {
        return $this->request->user;
    }

    /**
     * @inheritDoc
     */
    public function getSubject()
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return [
            'erasure-request' => $this->request->id
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getType()
    {
        return 'gdpr_erasure_cancelled';
    }

    /**
     * @inheritDoc
     */
    public static function getSubjectModel()
    {
        return ErasureRequest::class;
    }

    /**
     * @inheritDoc
     */
    public function getEmailView()
    {
        return 'gdpr::erasure-cancelled';
    }

    /**
     * @inheritDoc
     */
    public function getEmailSubject(TranslatorInterface $translator)
    {
        return $translator->trans('blomstra-gdpr.email.erasure_cancelled.subject');
    }
}
