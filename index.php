<?php
	/**
	 * Font Manager
	 * @version 1.0
	 * @author regiohelden
	 *
	 * @License: GPL2 
	 * Font Manager is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 2 of the License, or
	 * any later version.
	 *
	 * Font Manager is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with Font Manager. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.

	 **/

	if ( ! defined( 'FCL_SLUG' ) ) {
		define( 'FCL_SLUG', 'font-customizer-library' ); // The slug used for translations
	}

	if ( ! defined( 'ABSPATH' ) ) {
		die();
	}

	class FontManager {

		public $fcl = array();
		public $section_priority = 40;
		public $typekit = '';

		/**
		 * Start the FCL manager
		 * @since 1.0
		 *
		 * @param (array) $args Arguments to pass
		 *                       - 'section_priority' => (int) the priority of the FontManager in the customizer
		 *
		 * @return (void)
		 **/
		public function __construct( $args = array() ) {
			$defaults = array(
				'section_priority' => 40,
			);
			$args = wp_parse_args( $args, $defaults );
			$this->section_priority = $args['section_priority'];

			add_action( 'wp_footer', array( $this, 'output' ) );
			add_action( 'customize_register', array( $this, 'customizer' ) );
		}

		/**
		 * Adds a setting
		 * @since 1.0
		 *
		 * @param (string) $id          The ID, needs to be unique and will be used to store and retrieve the data. 
		 *                              Should be lowerstring, can contain a-z, 0-9, - and _.
		 * @param (string) $element     The CSS element, which is supposed to be changed by the Customizer.
		 * @param (string) $title       The title shown in the Customizer.
		 * @param (string) $description The description shown in the Customizer.
		 * @param (array)  $args        The arguments:
		 *                               - 'font-size'   => (boolean)       shows the font size input field.
		 *                               - 'font-weight' => (boolean|array) shows the font weight input field, via array you can define different weights.
		 *                               - 'font-family' => (boolean|array) shows the font family selectbox, via array you can define the fonts to show up.
		 *                               - 'line-height' => (boolean)       shows the line height input field.
		 *
		 * @return (boolean|WP_Error) returns true or a WP_Error object describing the problem.
		 **/
		public function add( $id, $element, $title, $description = '', $args = array() ) {
			if ( empty( $id ) ) {
				return new WP_Error( 'no-id', __( 'You need to define an ID.', FCL_SLUG ) );
			}

			if ( empty( $element ) ) {
				return new WP_Error( 'no-element', __( 'You need to define a CSS element.', FCL_SLUG ) );
			}

			if ( empty( $title ) ) {
				return new WP_Error( 'no-name', __( 'You need to name the customizer.', FCL_SLUG ) );
			}

			//We sanitize the ID
			$id = sanitize_key( $id );

			$default = array(
				'font-size'   => true,
				'font-weight' => true,
				'font-family' => true,
				'line-height' => true,
			);
			$args = wp_parse_args( $args, $default );

			$default_fonts = array(
				array(
					'id'    => 'open-sans',
					'name'  => 'Open Sans',
					'value' => '"Open Sans"',
					'src'   => 'https://fonts.googleapis.com/css?family=Open+Sans',
				),
				array(
					'id'    => 'roboto',
					'name'  => 'Roboto',
					'value' => '"Roboto"',
					'src'   => 'https://fonts.googleapis.com/css?family=Roboto',
				),
				array(
					'id'    => 'helvetica',
					'name'  => 'Helvetica',
					'value' => 'Helvetica',
					'src'   => false,
				),
			);

			/**
			 * Filters the default fonts.
			 * @since 1.0
			 *
			 * @param (array)  $default_fonts The fonts to filter
			 * @param (string) $id            The ID.
			 * @param (string) $element       The CSS element.
			 * @param (string) $title         The title.
			 * @param (array)  $args          The arguments.
			 *
			 * @return (array) $default_fonts
			 **/
			$default_fonts = apply_filters( 'fcl::default-fonts', $default_fonts, $id, $element, $title, $args );


			if ( true === $args['font-family'] ) {
				$args['font-family'] = $default_fonts;
			} elseif ( is_array( $args['font-family'] ) ) {
				foreach ( $args['font-family'] as $key => $font ) {

					//When only the name is given, we load the font from the default fonts by name
					if ( is_string( $font ) ) {
						foreach ( $default_fonts as $default_font ) {
							if ( $default_font['name'] == $font ) {
								$args['font-family'][ $key ] = $default_font;
							}

							if ( is_string( $args['font-family'][ $key ] ) ) {
								return new WP_Error( 'font-not-registered', sprintf( __( 'The font "%s" is not registered.', FCL_SLUG ) ) );
							}
						}
					}
				}
			}

			$default_font_weight = array(
				array(
					'id'    => 'normal',
					'name'  => __( 'Normal 400', FCL_SLUG ),
					'value' => 'normal',
				),
				array(
					'id'    => 'bold',
					'name' => __( 'Bold 600', FCL_SLUG ),
					'value' => 'bold',
				),
			);
			/**
			 * Filters the default fonts.
			 * @since 1.0
			 *
			 * @param (array)  $default_font_weight The fonts to filter
			 * @param (string) $id            The ID.
			 * @param (string) $element       The CSS element.
			 * @param (string) $title         The title.
			 * @param (array)  $args          The arguments.
			 *
			 * @return (array) $default_fonts
			 **/
			$default_font_weight = apply_filters( 'fcl::default-font-weight', $default_font_weight, $id, $element, $title, $args );


			if ( true === $args['font-weight'] ) {
				$args['font-weight'] = $default_font_weight;
			} elseif ( is_array( $args['font-weight'] ) ) {
				foreach ( $args['font-weight'] as $key => $weight ) {

					//When only the name is given, we load the font weight from the default weights by name
					if ( is_string( $weight ) ) {
						foreach ( $default_font_weight as $font_weight ) {
							if ( $font_weight['name'] == $weight ) {
								$args['font-weight'][ $key ] = $font_weight;
							}

							if ( is_string( $args['font-weight'][ $key ] ) ) {
								return new WP_Error( 'font-weight-not-registered', sprintf( __( 'The font weight "%s" is not registered.', FCL_SLUG ) ) );
							}
						}
					}

				}
			}

			/**
			 * Filters the arguments before passing to the customizer.
			 * @since 1.0
			 *
			 * @param (array) $args     The arguments to filter.
			 * @param (string) $id      The ID.
			 * @param (string) $element The element.
			 * @param (string) $name    The name.
			 *
			 * @return (array) $args
			 **/
			$args = apply_filters( 'fcl::args', $args, $id, $element, $title );

			$new_setting = array(
				'id'          => $id,
				'element'     => $element,
				'title'       => $title,
				'description' => $description,
				'args'        => $args,
			);

			$this->fcl[ $id ] = $new_setting;
			return true;
		}

		/**
		 * Adds a typekit ID to load typekit fonts.
		 * @since 1.0
		 *
		 * @param (string) $url The URL to the Typekit script
		 *
		 * @return (boolean) true
		 **/
		public function add_typekit( $url ) {
			$this->typekit = $url;
			return true;
		}

		/**
		 * Prints the styles.
		 * @since 1.0
		 *
		 * @return (void)
		 **/
		public function output() {

			if ( count( $this->fcl ) == 0 ) {
				return;
			}

			$render = array();
			$font_links = array();
			foreach ( $this->fcl as $section_id => $fcl ) {
				$render[ $fcl['element'] ] = array();
				foreach( $fcl['args'] as $property => $settings ) {
					switch ( $property ) {
						case 'font-size':
							$style = get_theme_mod(
								'fcl-font-size-' . $section_id
							);
							break;
						
						case 'line-height':
							$style = get_theme_mod(
								'fcl-line-height-' . $section_id
							);
							break;
				
						case 'font-weight':
							$style = get_theme_mod(
								'fcl-font-weight-' . $section_id
							);
							if ( ! $style ) {
								break;
							}

							foreach ( $settings as $setting ) {
								if ( $setting['id'] == $style ) {
									$style = $setting['value'];
									break;
								}
							}
							break;

						case 'font-family':
							$font_id = get_theme_mod(
								'fcl-font-family-' . $section_id
							);

							if ( ! $font_id ) {
								break;
							}

							foreach ( $settings as $setting ) {
								if ( $setting['id'] == $font_id ) {
									$style = $setting['value'];
									break;
								}
							}

							if ( false !== $setting['src'] ) {
								$font_links[] = $setting['src'];
							}

							$font_id = get_theme_mod(
								'fcl-font-family-fallback-' . $section_id
							);

							if ( ! $font_id ) {
								break;
							}

							foreach ( $settings as $setting ) {
								if ( $setting['id'] == $font_id ) {
									$fallback_style = $setting['value'];
									break;
								}
							}

							if ( false !== $setting['src'] ) {
								$font_links[] = $setting['src'];
							}

							//We do not need a fallback if the fallback equals the primary font
							if( $fallback_style != $style ) {
								$style .= ',' . $fallback_style;
							}
							break;
					}

					if ( ! empty( $style ) ) {
						$rendered[ $fcl['element'] ][ $property ] = $style;
					}
				}
			}

			if ( count( $rendered ) == 0 ) {
				return;
			}

			if ( count( $font_links ) > 0 ) {
				$font_links = array_unique( $font_links );
				foreach( $font_links as $font ) {
					echo '<link class="fcl-font" rel="stylesheet" href="' . $font . '">';
				}
			}

			echo '<style id="fcl">';
			foreach ( $rendered as $element => $properties ) {
				echo $element . '{';
				foreach ( $properties as $property => $style ) {
					echo $property . ':' . $style . ';';
				}
				echo '}';
			}
			echo '</style>';



			if( ! empty( $this->typekit ) ) {
				echo '<script src="' . $this->typekit . '"></script><script>try{Typekit.load();}catch(e){}</script>';
			}
		}

		/**
		 * The customizer settings
		 * @since 1.0
		 * 
		 * @param (object) $wp_customizer The customizer object.
		 *
		 * @return (void)
		 **/
		public function customizer( $wp_customize ) {
			if ( count( $this->fcl ) == 0 ) {
				return;
			}

			$wp_customize->add_panel(
				'fcl',
				array(
					'title'       => __( 'Fonts Manager', FCL_SLUG ),
					'priority'    => $this->section_priority,
					'description' => __( 'Manage the appearence of your fonts.', FCL_SLUG ),
				)
			);

			foreach ( $this->fcl as $section_id => $fcl ) {
				$wp_customize->add_section( 
					'fcl-section-' . $section_id, 
					array(
						'priority'       => 10,
						'capability'     => 'edit_theme_options',
						'title'          => $fcl['title'],
						'description'    => $fcl['description'],
						'panel'          => 'fcl',
					) 
				);

				foreach ( $fcl['args'] as $property => $setting ) {
					switch ( $property ) {
						case "font-size":
							$wp_customize->add_setting(
								'fcl-font-size-' . $section_id,
								array(
									'default' => ''
								)
							);

							$wp_customize->add_control(
								'fcl-font-size-controller-' . $section_id, 
								array(
									'label'       => __( 'Font size', FCL_SLUG ),
									'description' => __( 'Change the font size of the element.', FCL_SLUG ),
									'section'     => 'fcl-section-' . $section_id,
									'settings'    => 'fcl-font-size-' . $section_id,
									'type'        => 'text',
								)
							);
							break;

						case "line-height":

							$wp_customize->add_setting(
								'fcl-line-height-' . $section_id,
								array(
									'default' => 1,
								)
							);

							$wp_customize->add_control(
								'fcl-line-height-controller-' . $section_id, 
								array(
									'label'       => __( 'Line height', FCL_SLUG ),
									'description' => __( 'Change the line height of the element.', FCL_SLUG ),
									'section'     => 'fcl-section-' . $section_id,
									'settings'    => 'fcl-line-height-' . $section_id,
									'type'        => 'text',
								)
							);
							break;

						case "font-weight":
							$wp_customize->add_setting(
								'fcl-font-weight-' . $section_id,
								array(
									'default' => 'normal',
								)
							);

							$choices = array();
							if ( ! is_array( $setting ) ) {
								break;
							}

							foreach ( $setting as $choice ) {
								$choices[ $choice['id'] ] = $choice['name'];
							}

							$wp_customize->add_control(
								'fcl-font-weight-controller-' . $section_id, 
								array(
									'label'       => __( 'Font weight', FCL_SLUG ),
									'description' => __( 'Change the font weight of the element.', FCL_SLUG ),
									'section'     => 'fcl-section-' . $section_id,
									'settings'    => 'fcl-font-weight-' . $section_id,
									'type'        => 'select',
									'choices'     => $choices,
								)
							);
							break;
				
						case "font-family":
							$choices = array();
							if ( ! is_array( $setting ) ) {
								break;
							}

							foreach ( $setting as $choice ) {
								$choices[ $choice['id'] ] = $choice['name'];
							}

							$wp_customize->add_setting(
								'fcl-font-family-' . $section_id,
								array(
									'default' => '',
								)
							);
							$wp_customize->add_control(
								'fcl-font-family-controller-' . $section_id, 
								array(
									'label'       => __( 'Font family', FCL_SLUG ),
									'description' => __( 'Change the font family of the element.', FCL_SLUG ),
									'section'     => 'fcl-section-' . $section_id,
									'settings'    => 'fcl-font-family-' . $section_id,
									'type'        => 'select',
									'choices'     => $choices,
								)
							);

							$wp_customize->add_setting(
								'fcl-font-family-fallback-' . $section_id,
								array(
									'default' => '',
								)
							);

							$wp_customize->add_control(
								'fcl-font-family-fallback-controller-' . $section_id, 
								array(
									'label'       => __( 'Font fallback', FCL_SLUG ),
									'description' => __( 'Change the fallback font of the element.', FCL_SLUG ),
									'section'     => 'fcl-section-' . $section_id,
									'settings'    => 'fcl-font-family-fallback-' . $section_id,
									'type'        => 'select',
									'choices'     => $choices,
								)
							);
							break;
						}
					}
				}
			}
		}