<?php
namespace Database\Seeds\Shared;

use Illuminate\Database\Seeder;
use App\Models\SecretQuestions;

class SecretQuestionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SecretQuestions::truncate();

        $questions = [
            "What was the name of your second pet?",
            "What did you want to be when you grew up?",
            "Where were you on New Year’s 2000?",
            "What is the first name of the person you first kissed?",
            "What primary school did you attend?",
            "In what town or city was your first full time job?",
            "Where did you meet your spouse/partner?",
            "What is the middle name of your oldest child?",
            "What is your grandmother’s maiden name?",
            "What is your spouse/partner’s mother’s maiden name?",
            "In what city did your mother and father meet?",
        ];

        $questions_actual = [
            "What was the last name of your favorite teacher?",
            "Who was your childhood hero?",
            "What did you do as your first job?",
            "What was your dream job as a child?",
            "What was the first concert you attended?",
            "What date is your anniversary?",
            "What is your mother's middle name?",
            "What color was your first car?",
            "Where is your favorite vacation spot?",
            "What type of music do you like?",
            "What was your high school mascot?"
        ];
        
        foreach ($questions as $question) {
            $secretQuestions = new SecretQuestions();
            $secretQuestions->question = $question;
            $secretQuestions->actual = false;
            $secretQuestions->save();
        }

        foreach ($questions_actual as $question_a) {
            $secretQuestions = new SecretQuestions();
            $secretQuestions->question = $question_a;
            $secretQuestions->actual = true;
            $secretQuestions->save();
        }
    }
}
