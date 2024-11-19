<?php
/**
 * Plugin Name: WooCommerce Product Reviews Plugin
 * Description: Ajoute des avis fictif personnalisés sur les produits WooCommerce.
 * Version: 1.0
 * Author: FinaritraRak
 */

if (!defined('ABSPATH')) {
    exit; // Empêche un accès direct.
}

// Ajouter un menu personnalisé pour le plugin
add_action('admin_menu', 'prp_add_admin_menu');

function prp_add_admin_menu() {
    add_menu_page(
        'Avis Produits',              // Titre de la page
        'Avis Produits',              // Titre du menu
        'manage_options',             // Capacité requise
        'prp_reviews_dashboard',      // Slug de la page
        'prp_display_admin_page',     // Fonction de rappel pour afficher le contenu
        'dashicons-star-filled',      // Icône du menu
        25                            // Position dans le menu
    );
}

function prp_display_admin_page() {
    // Inclure Tailwind CDN
    echo '<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">';

    ?>
    <div class="wrap max-w-4xl mx-auto mt-8">
        <h1 class="text-3xl font-bold mb-6">Avis sur les produits</h1>

        <h2 class="text-2xl font-semibold mb-4">Ajouter un avis</h2>
        <form method="post" action="" class="space-y-4 bg-white p-6 shadow rounded-lg">
            <?php wp_nonce_field('prp_add_fake_review', 'prp_nonce'); ?>

            <div>
                <label for="product_id" class="block font-medium text-gray-700 mb-2">Sélectionnez un produit :</label>
                <select id="product_id" name="product_id" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <?php
                    $products = get_posts(array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                    ));
                    foreach ($products as $product) {
                        echo '<option value="' . $product->ID . '">' . esc_html($product->post_title) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div>
                <label for="review_author" class="block font-medium text-gray-700 mb-2">Nom de l'auteur :</label>
                <input type="text" id="review_author" name="review_author" required placeholder="John Doe" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="review_rating" class="block font-medium text-gray-700 mb-2">Note :</label>
                <select id="review_rating" name="review_rating" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="5">5 étoiles</option>
                    <option value="4">4 étoiles</option>
                    <option value="3">3 étoiles</option>
                    <option value="2">2 étoiles</option>
                    <option value="1">1 étoile</option>
                </select>
            </div>

            <div>
                <label for="review_content" class="block font-medium text-gray-700 mb-2">Avis :</label>
                <textarea id="review_content" name="review_content" rows="4" required placeholder="Votre avis ici..." class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none">Ajouter l'avis</button>
        </form>

        <hr class="my-8">

        <h2 class="text-2xl font-semibold mb-4">Liste des avis existants</h2>
        <table class="min-w-full bg-white shadow rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Auteur</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Produit</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Avis</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Note</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php
                $reviews = get_comments(array('type' => 'review', 'status' => 'approve'));
                if ($reviews) {
                    foreach ($reviews as $review) {
                        $product = get_post($review->comment_post_ID);
                        $rating = get_comment_meta($review->comment_ID, 'rating', true);
                        ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo esc_html($review->comment_author); ?></td>
                            <td class="px-4 py-2"><a href="<?php echo get_edit_post_link($product->ID); ?>" class="text-blue-500 hover:underline"><?php echo esc_html($product->post_title); ?></a></td>
                            <td class="px-4 py-2"><?php echo esc_html($review->comment_content); ?></td>
                            <td class="px-4 py-2"><?php echo intval($rating); ?> / 5</td>
                            <td class="px-4 py-2"><?php echo esc_html($review->comment_date); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="5" class="px-4 py-2 text-center text-gray-500">Aucun avis trouvé.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php

    // Ajouter un avis fictif si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prp_nonce']) && wp_verify_nonce($_POST['prp_nonce'], 'prp_add_fake_review')) {
        prp_add_fake_review();
    }
}

function prp_add_fake_review() {
    if (!isset($_POST['product_id'], $_POST['review_author'], $_POST['review_rating'], $_POST['review_content'])) {
        return;
    }

    $product_id = intval($_POST['product_id']);
    $author = sanitize_text_field($_POST['review_author']);
    $rating = intval($_POST['review_rating']);
    $content = sanitize_textarea_field($_POST['review_content']);

    $comment_data = array(
        'comment_post_ID' => $product_id,
        'comment_author' => $author,
        'comment_content' => $content,
        'comment_type' => 'review',
        'comment_approved' => 1,
    );

    $comment_id = wp_insert_comment($comment_data);

    if ($comment_id) {
        add_comment_meta($comment_id, 'rating', $rating);
        echo '<div class="notice notice-success is-dismissible"><p>Avis ajouté avec succès !</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Erreur lors de l\'ajout de l\'avis.</p></div>';
    }
}
