<?php

namespace App\Http\Controllers\Traits;

trait StrManipulationTrait
{
    /**
     * Hide Email
     *
     * @param string $email
     *
     * @return bool
     */
    private function hideEmail(string $email)
    {
        $email = explode('@', $email);
        $str_count = strlen($email[0]);
        $generated_str = $this->generateStr('*', $str_count);

        switch ($str_count) {
            case 1:
            case 2:
            case 3:
                $email[0] = $email[0]{0} . $generated_str;
                break;
            case 4:
            case 5:
                $email[0] = $email[0]{0} . $generated_str . $email[0]{$str_count - 1};
                break;
            case 6:
                $email[0] = $email[0]{0} . $email[0]{1} . $generated_str . $email[0]{$str_count - 1};
                break;
            default:
                $email[0] = $email[0]{0} . $email[0]{1} . $generated_str . $email[0]{$str_count - 2} . $email[0]{$str_count - 1};
                break;
        }

        return implode('@', $email);
    }

    /**
     * Generate Str
     *
     * @param string $str
     * @param int $count
     *
     * @return bool
     */
    private function generateStr(string $str, int $count)
    {
        $new_str = $str;
        $rand = rand($count, $count + 4) / 2;

        for ($i = 0; $i <= $rand; $i++) {
            $new_str .= $str;
        }

        return $new_str;
    }
}