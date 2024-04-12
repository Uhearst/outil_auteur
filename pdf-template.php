<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/syllabusstyle.css"/>
</head>
<body>

<div class="udeha-syllabus-logo">
    <?php if(array_key_exists('logo', $formattedValues)): ?>
        <img src="<?php echo $formattedValues['logo']; ?>">
    <?php endif; ?>
    <div class="udeha-title-courseplan"><?php print_string('courseplan', 'format_udehauthoring') ?></div>
</div>

<?php echo $this->get_presentation_content_syllabus(); ?>

<div class="udeha-page-end"></div>

<?php if (!empty($this->get_desc_content())): ?>
<div class="udeha-page-block">
    <div class="h3title"><?php print_string('coursedescription', 'format_udehauthoring'); ?></div>
    <div class="info-data full"><?php echo $formattedValues && array_key_exists('description', $formattedValues) ? $formattedValues['description'] : $this->get_desc_content(); ?></div>
</div>
<?php endif; ?>



<?php if (!empty($this->get_objectives_content_syllabus())): ?>
<div class="udeha-page-block">
    <div class="h3title"><?php print_string('teachingobjectives', 'format_udehauthoring'); ?></div>
    <div class="info-data full"><?php echo $this->get_objectives_content_syllabus(true); ?></div>
</div>
<div class="udeha-page-end"></div>
<?php endif; ?>



<div class="udeha-page-block">
    <?php if (!empty($this->get_problematic_content())): ?>
        <div class="h3title"><?php print_string('syllabustitle_problematic', 'format_udehauthoring'); ?></div>
        <div class="info-data full"><?php echo $formattedValues && array_key_exists('problematic', $formattedValues) ? $formattedValues['problematic'] : $this->get_problematic_content(); ?></div>
        <br />
    <?php endif; ?>

    <?php if (!empty($this->get_place_content())): ?>
        <div class="h3title"><?php print_string('courseplace', 'format_udehauthoring'); ?></div>
        <div class="info-data full"><?php echo $formattedValues && array_key_exists('place', $formattedValues) ? $formattedValues['place'] : $this->get_place_content(); ?></div>
        <br />
    <?php endif; ?>

    <?php if (!empty($this->get_method_content())): ?>
        <div class="h3title"><?php print_string('syllabustitle_method', 'format_udehauthoring'); ?></div>
        <div class="info-data full"><?php echo $formattedValues && array_key_exists('method', $formattedValues) ? $formattedValues['method'] : $this->get_method_content(); ?></div>
        <br />
    <?php endif; ?>
</div>

<div class="udeha-page-end"></div>

<?php if (!empty($this->get_modules_content())): ?>
<div class="udeha-page-block">
    <div class="h3title"><?php print_string('modulescontent', 'format_udehauthoring', strtolower($DB->get_record('udehauthoring_title', ['id' => $this->courseplan->id])->module)); ?></div>
    <?php echo $this->get_modules_content(true); ?>
</div>
<?php endif; ?>

<div class="udeha-page-end"></div>

<?php if (!empty($this->get_evaluations_content())): ?>
<div class="udeha-page-block">
    <div class="h3title"><?php print_string('evaluations', 'format_udehauthoring'); ?></div>
    <?php echo $this->get_evaluations_content(true); ?>
</div>
<?php endif; ?>

<div class="udeha-page-end"></div>

<?php if (!empty($this->get_extra_content())): ?>
<div class="udeha-page-block">
    <div class="h3title"><?php print_string('extrainfo', 'format_udehauthoring'); ?></div>
    <div class="info-data full"><?php echo $this->get_extra_content(true); ?></div>
</div>
<?php endif; ?>
<footer>
    <div class="pagenum-container"><span class="pagenum"></span></div>
</footer>
<header>
    <img src="assets/SignatureMarque-1ligne_UdeH_Couleur_RVB_small.png" width="200" />
</header>
</body>
</html>