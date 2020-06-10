<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\LessonComments;

class LessonCommentMention extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $lessonComments;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param LessonComments $lessonComments
     */
    public function __construct(User $user, LessonComments $lessonComments)
    {
        $this->user = $user;
        $this->lessonComments = $lessonComments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $protokol = env('CMS_DOMAIN_SECURE', false) ? 'https:' : 'http:';
        $url = $protokol . '://' . env('EMAIL_DOMAIN');
        return $this->view('emails.lesson-comment-mention')
            ->subject("LearnHub: Mentioned in the comment")
            ->with([
                'CourseName' => $this->lessonComments->lesson->course->name,
                'CommentedFName' => $this->lessonComments->user->first_name,
                'Comment' => $this->lessonComments->comment,
                'DiscusURL' => "{$url}/profile/course/{$this->lessonComments->lesson->course->id}/view/lesson/{$this->lessonComments->lesson->id}"
            ]);
    }
}
