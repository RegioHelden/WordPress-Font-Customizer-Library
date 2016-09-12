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

	if ( ! defined( 'FM_SLUG' ) ) {
		define( 'FM_SLUG', 'font-manager' ); // The slug used for translations
	}

	if ( ! defined( 'ABSPATH' ) ) {
		die();
	}

	class FontManager {

		public $font_manager = array();
		public $section_priority = 40;
		public $typekit = '';
		public $defaults = array();		//Contains the default values for 'font-size', 'font-weight', 'line-height' and 'font-family'

		/**
		 * Start the Font manager
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

			/* Filters the default values.
			 * @since 1.0
			 * 
			 * @param (array) $defaults The default values
			 * @param (array) $args     The arguments
			 *
			 * @return (array) $defaults
			 **/
			$defaults = array(
				'font-size'   => '16px',
				'font-weight' => 'normal',
				'line-height' => '1',
				'font-family' => 'open-sans',
				'color'       => '#000',
			);
			$this->defaults = apply_filters( 'font-manager::defaults', $defaults, $args );

			//Hook into the actions
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
				return new WP_Error( 'no-id', __( 'You need to define an ID.', FM_SLUG ) );
			}

			if ( empty( $element ) ) {
				return new WP_Error( 'no-element', __( 'You need to define a CSS element.', FM_SLUG ) );
			}

			if ( empty( $title ) ) {
				return new WP_Error( 'no-name', __( 'You need to name the customizer.', FM_SLUG ) );
			}

			//We sanitize the ID
			$id = sanitize_key( $id );

			$default = array(
				'font-size'   => true,
				'font-weight' => true,
				'font-family' => true,
				'line-height' => true,
				'color'       => true,
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
			$default_fonts = apply_filters( 'font-manager::default-fonts', $default_fonts, $id, $element, $title, $args );

			if ( true === $args['font-family']  ) {
				$args['font-family'] = array(
					'values' => true,
				);
			}

			if ( isset( $args['font-family']['values'] ) && true === $args['font-family']['values'] ) {
				$args['font-family']['values'] = $default_fonts;
			} elseif ( ! empty( $args['font-family']['values'] ) && is_array( $args['font-family']['values'] ) ) {
				foreach ( $args['font-family']['values'] as $key => $font ) {

					//When only the name is given, we load the font from the default fonts by name
					if ( is_string( $font ) ) {
						foreach ( $default_fonts as $default_font ) {
							if ( $default_font['name'] == $font ) {
								$args['font-family']['values'][ $key ] = $default_font;
							}

							if ( is_string( $args['font-family']['values'][ $key ] ) ) {
								return new WP_Error( 'font-not-registered', sprintf( __( 'The font "%s" is not registered.', FM_SLUG ) ) );
							}
						}
					}
				}
			}

			$default_font_weight = array(
				array(
					'id'    => 'normal',
					'name'  => __( 'Normal 400', FM_SLUG ),
					'value' => 'normal',
				),
				array(
					'id'    => 'bold',
					'name' => __( 'Bold 600', FM_SLUG ),
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
			$default_font_weight = apply_filters( 'font-manager::default-font-weight', $default_font_weight, $id, $element, $title, $args );

			if ( true === $args['font-weight']  ) {
				$args['fonts-weight'] = array(
					'values' => true,
				);
			}

			if ( isset( $args['font-weight']['values'] ) && true === $args['font-weight']['values'] ) {
				$args['font-weight']['values'] = $default_font_weight;
			} elseif ( ! empty( $args['font-weight']['values'] ) && is_array( $args['font-weight']['values'] ) ) {
				foreach ( $args['font-weight']['values'] as $key => $weight ) {

					//When only the name is given, we load the font weight from the default weights by name
					if ( is_string( $weight ) ) {
						foreach ( $default_font_weight as $font_weight ) {
							if ( $font_weight['name'] == $weight ) {
								$args['font-weight']['values'][ $key ] = $font_weight;
							}

							if ( is_string( $args['font-weight']['values'][ $key ] ) ) {
								return new WP_Error( 'font-weight-not-registered', sprintf( __( 'The font weight "%s" is not registered.', FM_SLUG ) ) );
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
			$args = apply_filters( 'font-manager::args', $args, $id, $element, $title );

			$new_setting = array(
				'id'          => $id,
				'element'     => $element,
				'title'       => $title,
				'description' => $description,
				'args'        => $args,
			);

			$this->font_manager[ $id ] = $new_setting;
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

			if ( count( $this->font_manager ) == 0 ) {
				return;
			}

			$render = array();
			$font_links = array();
			foreach ( $this->font_manager as $section_id => $fmng ) {
				$render[ $fmng['element'] ] = array();
				foreach( $fmng['args'] as $property => $settings ) {
					switch ( $property ) {
						case 'color':
							$style = get_theme_mod(
								'fmng-color-' . $section_id
							);
							break;
						case 'font-size':
							$style = get_theme_mod(
								'fmng-font-size-' . $section_id
							);
							break;
						
						case 'line-height':
							$style = get_theme_mod(
								'fmng-line-height-' . $section_id
							);
							break;
				
						case 'font-weight':
							$style = get_theme_mod(
								'fmng-font-weight-' . $section_id
							);
							if ( ! $style ) {
								break;
							}

							foreach ( $settings['values'] as $setting ) {
								if ( $setting['id'] == $style ) {
									$style = $setting['value'];
									break;
								}
							}
							break;

						case 'font-family':
							$font_id = get_theme_mod(
								'fmng-font-family-' . $section_id
							);

							if ( ! $font_id ) {
								break;
							}

							foreach ( $settings['values'] as $setting ) {
								if ( $setting['id'] == $font_id ) {
									$style = $setting['value'];
									break;
								}
							}

							if ( false !== $setting['src'] ) {
								$font_links[] = $setting['src'];
							}

							$font_id = get_theme_mod(
								'fmng-font-family-fallback-' . $section_id
							);

							if ( ! $font_id ) {
								break;
							}

							foreach ( $settings['values'] as $setting ) {
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
						$rendered[ $fmng['element'] ][ $property ] = $style;
					}
				}
			}

			if ( count( $rendered ) == 0 ) {
				return;
			}

			if ( count( $font_links ) > 0 ) {
				$font_links = array_unique( $font_links );
				foreach( $font_links as $font ) {
					echo '<link class="fmng-font" rel="stylesheet" href="' . $font . '">';
				}
			}

			echo '<style id="fmng">';
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
			if ( count( $this->font_manager ) == 0 ) {
				return;
			}

			$wp_customize->add_panel(
				'fmng',
				array(
					'title'       => __( 'Fonts Manager', FM_SLUG ),
					'priority'    => $this->section_priority,
					'description' => __( 'Manage the appearence of your fonts.', FM_SLUG ),
				)
			);

			foreach ( $this->font_manager as $section_id => $fmng ) {
				$wp_customize->add_section( 
					'fmng-section-' . $section_id, 
					array(
						'priority'       => 10,
						'capability'     => 'edit_theme_options',
						'title'          => $fmng['title'],
						'description'    => $fmng['description'],
						'panel'          => 'fmng',
					) 
				);

				foreach ( $fmng['args'] as $property => $setting ) {
					switch ( $property ) {
						case "color":
							$default = $this->defaults['color'];
							if ( true !== $setting ) {
								$default = $setting;
							}
							$wp_customize->add_setting(
								'fmng-color-' . $section_id,
								array(
									'default' => $default,
								)
							);

							$wp_customize->add_control(
								new WP_Customize_Color_Control( 
									$wp_customize,
									'fmng-color-controller-' . $section_id,
									array(
										'label'      => __( 'Font color', FM_SLUG ),
										'section'    => 'fmng-section-' . $section_id,
										'settings'   => 'fmng-color-' . $section_id,
									)
								)
							);

							break;

						case "font-size":
							$default = $this->defaults['font-size'];
							if ( true !== $setting ) {
								$default = $setting;
							}
							$wp_customize->add_setting(
								'fmng-font-size-' . $section_id,
								array(
									'default' => $default,
								)
							);

							$wp_customize->add_control(
								'fmng-font-size-controller-' . $section_id, 
								array(
									'label'       => __( 'Font size', FM_SLUG ),
									'description' => __( 'Change the font size of the element.', FM_SLUG ),
									'section'     => 'fmng-section-' . $section_id,
									'settings'    => 'fmng-font-size-' . $section_id,
									'type'        => 'text',
								)
							);
							break;

						case "line-height":
							$default = $this->defaults['line-height'];
							if ( true !== $setting ) {
								$default = $setting;
							}
							$wp_customize->add_setting(
								'fmng-line-height-' . $section_id,
								array(
									'default' => $default,
								)
							);

							$wp_customize->add_control(
								'fmng-line-height-controller-' . $section_id, 
								array(
									'label'       => __( 'Line height', FM_SLUG ),
									'description' => __( 'Change the line height of the element.', FM_SLUG ),
									'section'     => 'fmng-section-' . $section_id,
									'settings'    => 'fmng-line-height-' . $section_id,
									'type'        => 'text',
								)
							);
							break;

						case "font-weight":
							$choices = array();
							if ( empty( $setting['values'] ) || ! is_array( $setting['values'] ) ) {
								break;
							}

							foreach ( $setting['values'] as $choice ) {
								$choices[ $choice['id'] ] = $choice['name'];
							}

							$default = $this->defaults['font-weight'];
							if ( ! empty( $setting['default'] ) ) {
								$default = $setting['default'];
							}

							$wp_customize->add_setting(
								'fmng-font-weight-' . $section_id,
								array(
									'default' => $default,
								)
							);

							$wp_customize->add_control(
								'fmng-font-weight-controller-' . $section_id, 
								array(
									'label'       => __( 'Font weight', FM_SLUG ),
									'description' => __( 'Change the font weight of the element.', FM_SLUG ),
									'section'     => 'fmng-section-' . $section_id,
									'settings'    => 'fmng-font-weight-' . $section_id,
									'type'        => 'select',
									'choices'     => $choices,
								)
							);
							break;
				
						case "font-family":
							$choices = array();
							if ( empty( $setting['values'] ) || ! is_array( $setting['values'] ) ) {
								break;
							}

							foreach ( $setting['values'] as $choice ) {
								$choices[ $choice['id'] ] = $choice['name'];
							}

							$default = $this->defaults['font-family'];
							if ( ! empty( $setting['default'] ) ) {
								$default = $setting['default'];
							}

							$wp_customize->add_setting(
								'fmng-font-family-' . $section_id,
								array(
									'default' => $default,
								)
							);
							$wp_customize->add_control(
								'fmng-font-family-controller-' . $section_id, 
								array(
									'label'       => __( 'Font family', FM_SLUG ),
									'description' => __( 'Change the font family of the element.', FM_SLUG ),
									'section'     => 'fmng-section-' . $section_id,
									'settings'    => 'fmng-font-family-' . $section_id,
									'type'        => 'select',
									'choices'     => $choices,
								)
							);

							$wp_customize->add_setting(
								'fmng-font-family-fallback-' . $section_id,
								array(
									'default' => $default,
								)
							);

							$wp_customize->add_control(
								'fmng-font-family-fallback-controller-' . $section_id, 
								array(
									'label'       => __( 'Font fallback', FM_SLUG ),
									'description' => __( 'Change the fallback font of the element.', FM_SLUG ),
									'section'     => 'fmng-section-' . $section_id,
									'settings'    => 'fmng-font-family-fallback-' . $section_id,
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