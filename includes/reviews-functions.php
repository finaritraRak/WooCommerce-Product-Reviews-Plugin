<?php


add_shortcode('add_review_form', 'prp_render_review_form');
function prp_render_review_form($atts)
{
    if (!is_product()) {
        return '<p>Ce formulaire est disponible uniquement sur les pages produits.</p>';
    }

    ob_start();
    ?>
    <form id="prp-review-form">
        <label for="review_author">Votre nom :</label>
        <input type="text" id="review_author" name="review_author" required>

        <label for="review_email">Votre email :</label>
        <input type="email" id="review_email" name="review_email" required>

        <label for="review_rating">Note :</label>
        <select id="review_rating" name="review_rating" required>
            <option value="5">5 étoiles</option>
            <option value="4">4 étoiles</option>
            <option value="3">3 étoiles</option>
            <option value="2">2 étoiles</option>
            <option value="1">1 étoile</option>
        </select>

        <label for="review_content">Votre avis :</label>
        <textarea id="review_content" name="review_content" required></textarea>

        <input type="hidden" name="product_id" value="<?php echo get_the_ID(); ?>">

        <button type="submit">Envoyer l'avis</button>
    </form>
    <div id="prp-response"></div>
    <?php
    return ob_get_clean();
}

// Gestion de la soumission d'avis en AJAX
add_action('wp_enqueue_scripts', 'prp_enqueue_scripts');
function prp_enqueue_scripts()
{
    wp_enqueue_script(
        'prp-review-script',
        plugin_dir_url(__FILE__) . '../assets/review-script.js',
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script('prp-review-script', 'prp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}

add_action('wp_ajax_prp_add_review', 'prp_handle_review_submission');
add_action('wp_ajax_nopriv_prp_add_review', 'prp_handle_review_submission');
function prp_handle_review_submission()
{
    if (!isset($_POST['product_id'], $_POST['review_author'], $_POST['review_email'], $_POST['review_content'], $_POST['review_rating'])) {
        wp_send_json_error('Données manquantes.');
    }

    $product_id = intval($_POST['product_id']);
    $author = sanitize_text_field($_POST['review_author']);
    $email = sanitize_email($_POST['review_email']);
    $content = sanitize_textarea_field($_POST['review_content']);
    $rating = intval($_POST['review_rating']);

    // Ajouter l'avis comme commentaire
    $comment_data = array(
        'comment_post_ID' => $product_id,
        'comment_author' => $author,
        'comment_author_email' => $email,
        'comment_content' => $content,
        'comment_type' => 'review',
        'comment_approved' => 1,
    );

    $comment_id = wp_insert_comment($comment_data);

    if ($comment_id) {
        add_comment_meta($comment_id, 'rating', $rating);
        wp_send_json_success('Avis ajouté avec succès.');
    } else {
        wp_send_json_error('Erreur lors de l\'ajout de l\'avis.');
    }
}
