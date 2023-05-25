<?php

/***************************************************************************
 *
 *    OUGC Pages plugin (/inc/languages/english/admin/ougc_pages.lang.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2014 - 2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Create additional pages directly from the ACP.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

// Plugin API
$l['setting_group_ougc_pages'] = 'OUGC Pages';
$l['setting_group_ougc_pages_desc'] = 'Crea paginas adicionales directamente desde tu ACP.';

// Settings
$l['setting_ougc_pages_portal'] = 'Usar Script del Portal';
$l['setting_ougc_pages_portal_desc'] = 'Selecciona si quieres mostrar las paginas dentro del portal.';
$l['setting_ougc_pages_seo'] = 'Usar Enlaces Amigables SEO';
$l['setting_ougc_pages_seo'] = 'Activa la opcion para usar enlaces amigables al SEO.';
$l['setting_ougc_pages_seo_scheme'] = 'Esquema de URL de Pagina';
$l['setting_ougc_pages_seo_scheme_desc'] = 'Escribe el esquema del enlace de las paginas. Deja vacio para deshabilitar los enlaces SEO para las paginas.';
$l['setting_ougc_pages_seo_scheme_categories'] = 'Esquema de URL de Categorias';
$l['setting_ougc_pages_seo_scheme_categories_desc'] = 'Escribe el esquema del enlace de las categorias. Deja vacio para deshabilitar los enlaces SEO para las categorias.';
$l['setting_ougc_pages_perpage'] = 'Objetos por Pagina';
$l['setting_ougc_pages_perpage_desc'] = 'Maximo numero de objetos a mostrar por pagina en el ACP.';

// ACP
$l['ougc_pages_manage'] = 'Administrar Paginas';
$l['ougc_pages_manage_desc'] = 'Esta seccion te permite adminsitrar tus paginas personalizadas.';
$l['ougc_pages_tab_add'] = 'Agregar Pagina';
$l['ougc_pages_tab_add_desc'] = 'Aqui puedes agregar una pagina nueva.';
$l['ougc_pages_tab_import'] = 'Importar Pagina';
$l['ougc_pages_tab_import_desc'] = 'Utiliza esta seccion para importar nuevas paginas.';
$l['ougc_pages_tab_edit'] = 'Editar Pagina';
$l['ougc_pages_tab_edit_desc'] = 'Aqui puedes editar una pagina existente.';
$l['ougc_pages_tab_edit_cat'] = 'Editar Categoria';
$l['ougc_pages_tab_edit_cat_desc'] = 'Aqui puedes editar una categoria existente.';
$l['ougc_pages_tab_cat'] = 'Categorias';
$l['ougc_pages_tab_cat_desc'] = 'Esta seccion te permite adminsitrar tus categorias.';
$l['ougc_pages_tab_cat_add'] = 'Agregar Categoria';
$l['ougc_pages_tab_cat_add_desc'] = 'Aqui puedes agregar una categoria.';
$l['ougc_pages_view_empty'] = 'Actualmente no hay paginas para mostrar.';
$l['ougc_pages_form_category'] = 'Categoria';
$l['ougc_pages_form_category_desc'] = 'Selecciona la categoria asignada a esta pagina.';
$l['ougc_pages_form_name'] = 'Nombre';
$l['ougc_pages_form_name_desc'] = 'Escribe el nombre de la pagina o categoria.';
$l['ougc_pages_form_description'] = 'Descripcion';
$l['ougc_pages_form_description_desc'] = 'Escribe la descripcion de la pagina o categoria.';
$l['ougc_pages_form_url'] = 'Unique URL';
$l['ougc_pages_form_url_desc'] = 'Escribe la identificacion de URL unica de la pagina o categoria.';
$l['ougc_pages_form_import'] = 'Archivo Local';
$l['ougc_pages_form_import_desc'] = 'Selecciona un archivo XML para importar tu pagina desde tu maquina.';
$l['ougc_pages_form_import_url'] = 'URL de Archivo';
$l['ougc_pages_form_import_url_desc'] = 'Escribe la direccion web del archivo XML para importar tu pagina.';
$l['ougc_pages_form_import_ignore_version'] = 'Inorar Compatibilidad';
$l['ougc_pages_form_import_ignore_version_desc'] = 'Selecciona si esta pagina debe ser importada ignorando la version del plugin para la cual fue creada.';
$l['ougc_pages_form_category'] = 'Categoria';
$l['ougc_pages_form_disabled'] = 'Deshabilitado';
$l['ougc_pages_form_disabled_desc'] = 'Deshabilitado';
$l['ougc_pages_form_visible'] = 'Activar';
$l['ougc_pages_form_visible_desc'] = 'Esta categoria o pagina sera visible';
$l['ougc_pages_form_breadcrumb'] = 'Mostrar en Breadcrumb';
$l['ougc_pages_form_breadcrumb_desc'] = 'Esta categoria se mostrara en el rastro de breadcrumb del foro.';
$l['ougc_pages_form_wrapucp'] = 'Agregar al UserCP';
$l['ougc_pages_form_wrapucp_desc'] = 'Selecciona si quieres mostrar esta categoria como parte del panel de control de usuario.';
/*$l['ougc_pages_form_navigation'] = 'Show Navigation';
$l['ougc_pages_form_navigation_desc'] = 'Whether if to show a previous/next pagination in this category in pages.';*/
$l['ougc_pages_form_wol'] = 'Mostrar en Pagina Online';
$l['ougc_pages_form_wol_desc'] = 'Selecciona si quieres mostrar esta ubicacion en la pagina de usuarios en linea.';
$l['ougc_pages_form_wrapper'] = 'Usar el Template Wrapper';
$l['ougc_pages_form_wrapper_desc'] = 'El contenido de tu pagina sera embuelto en un template global.';
$l['ougc_pages_form_php'] = 'Codigo PHP';
$l['ougc_pages_form_php_desc'] = 'Selecciona si quieres procesar esta pagina como una pagina de PHP o de lo contrario utilizar el sistema de templates de MyBB.';
$l['ougc_pages_form_init'] = 'Ejecutar en Inicio';
$l['ougc_pages_form_init_desc'] = 'Las pagina sera ejecutada antes del inicio (global_end).';
$l['ougc_pages_form_template'] = 'Template';
$l['ougc_pages_form_template_desc'] = 'Escribe tu pagina abajo.';
$l['ougc_pages_form_disporder'] = 'Orden';
$l['ougc_pages_form_disporder_desc'] = 'Orden en el cual mostrar esta categoria o pagina.';
$l['ougc_pages_form_groups'] = 'Grupos Autorizados';
$l['ougc_pages_form_groups_desc'] = 'Selecciona los grupos que pueden ver esta categoria o pagina. Deja sin seleccionar para autorizar a todos los grupos.';
$l['ougc_pages_button_disponder'] = 'Actualizar Orden';
$l['ougc_pages_button_submit'] = 'Guardar y Regresar';
$l['ougc_pages_button_submit_continue'] = 'Guardar y Continuar';
$l['ougc_pages_form_export'] = 'Exportar';
$l['ougc_pages_view_page'] = 'Ver';

// ACP Module: Messages
$l['ougc_pages_error_update'] = 'OUGC Pages requiere ser actualizado. Por favor desactiva y activa de vuelta el plugin.';
$l['ougc_pages_error_add'] = 'Hubo un error al crear tu categoria.';
$l['ougc_pages_error_invalidname'] = 'El nombre escrito es invalido.';
$l['ougc_pages_error_invaliddescription'] = 'La descripcion escrita es invalida.';
$l['ougc_pages_error_invalidcategory'] = 'La categoria seleccionada es invalida.';
$l['ougc_pages_error_invalidurl'] = 'La URL debe de ser unica entre todas las paginas o es invalida.';
$l['ougc_pages_error_invalidimport'] = 'El contenido de la pagina es invalido.';
$l['ougc_pages_error_invalidversion'] = 'El contenido de la pagina parece ser de una version invalida.';
$l['ougc_pages_success_add'] = 'La categoria o pagina se creo exitosamente.';
$l['ougc_pages_success_edit'] = 'La categoria o pagina fue editada exitosamente.';
$l['ougc_pages_success_delete'] = 'La categoria o pagina se borro exitosamente.';

// Admin Permissions
$l['ougc_pages_config_permissions'] = 'Puede administrar las paginas.';

// PluginLibrary
$l['ougc_pages_pl_required'] = 'Este plugin requiere de <a href="{1}">PluginLibrary</a> version {2} o mayor para funcionar.';
$l['ougc_pages_pl_old'] = 'Este plugin requiere de <a href="{1}">PluginLibrary</a> version {2} o mayor para funcionar.';