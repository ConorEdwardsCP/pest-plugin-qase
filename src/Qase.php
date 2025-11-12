<?php

declare(strict_types=1);

namespace Pest\Qase;

use Pest\Qase\Traits\HasQaseMetadata;

class Qase
{
    use HasQaseMetadata;

    public function __construct(private QaseReporter $reporter) {}

    /*
     * Add comment to test case
     * @param string $message
     * @return void
     *
     * Example:
     * Qase::comment("My comment");
     */
    public function comment(string $message): self
    {
        $this->reporter->addComment($message);

        return $this;
    }

    /* Add attachment to test case
     * @param mixed $input
     * @return void
     *
     * Example:
     * Qase::attach("/my_path/file.json");
     * Qase::attach(["/my_path/file.json", "/my_path/file2.json"]);
     * Qase::attach((object) ['title' => 'attachment.txt', 'content' => 'Some string', 'mime' => 'text/plain']);
     */
    public function attach(mixed $input): self
    {
        $this->reporter->addAttachment($input);

        return $this;
    }

    protected function getReporter(): QaseReporter
    {
        return $this->reporter;
    }
}
