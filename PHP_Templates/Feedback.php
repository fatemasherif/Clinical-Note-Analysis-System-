<?php
require_once __DIR__ . '/BaseTemplate.php';

class Feedback extends BaseTemplate {
    private $message;

    public function __construct($message = '') {
        $this->message = $message;
        $content = $this->buildContent();
        parent::__construct('Feedback', $content);
    }

    private function buildContent() {
        $messageHtml = '';
        if ($this->message) {
            $messageClass = strpos($this->message, 'success') !== false ? 'success' : 'info';
            $messageHtml = '<div class="message ' . $messageClass . '" style="padding: 15px; margin-bottom: 20px; background: #d4edda; color: #155724; border-radius: 8px;">' . htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        return <<<HTML
<div style="max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
  <h2 style="color: #004aad; margin-bottom: 25px;">Share Your Feedback</h2>
  
  {$messageHtml}
  
  <form method="POST" action="feedback.php">
    <label for="rating" style="display: block; margin-top: 15px; font-weight: 500; color: #333;">Rate the System:</label>
    <div class="star-rating" id="starRating" style="display: flex; gap: 8px; font-size: 2.5rem; color: #ccc; cursor: pointer; margin: 10px 0;">
      <span class="star" data-value="1" style="transition: color 0.2s;">&#9733;</span>
      <span class="star" data-value="2" style="transition: color 0.2s;">&#9733;</span>
      <span class="star" data-value="3" style="transition: color 0.2s;">&#9733;</span>
      <span class="star" data-value="4" style="transition: color 0.2s;">&#9733;</span>
      <span class="star" data-value="5" style="transition: color 0.2s;">&#9733;</span>
    </div>
    <input type="hidden" name="rating" id="ratingValue" required />

    <label for="comments" style="display: block; margin-top: 15px; font-weight: 500; color: #333;">Your Comments:</label>
    <textarea name="comments" id="comments" placeholder="Tell us what you think..." required style="width: 100%; height: 100px; padding: 10px; margin-top: 8px; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; resize: vertical; box-sizing: border-box;"></textarea>

    <button type="submit" style="margin-top: 20px; background-color: #0077cc; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">Submit Feedback</button>
  </form>
</div>

<style>
  .star-rating .star.selected,
  .star-rating .star:hover,
  .star-rating .star:hover ~ .star {
    color: #ffb400;
  }
</style>

<script>
  const stars = document.querySelectorAll('.star-rating .star');
  const ratingInput = document.getElementById('ratingValue');

  stars.forEach((star, index) => {
    star.addEventListener('click', () => {
      ratingInput.value = star.dataset.value;
      stars.forEach((s, i) => {
        s.classList.toggle('selected', i < index + 1);
      });
    });
  });
</script>
HTML;
    }
}
?>