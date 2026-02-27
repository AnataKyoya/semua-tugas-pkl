<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WebsiteScraperBaru extends BaseConfig
{

    public $sites = [

        'inaexport' => [

            'start_url' => 'https://inaexport.id/suppliers?sorteks=asc&page=1',

            'pagination' => [
                'selector' => 'ul.pagination li.page-item .page-link',
                'max_page' => 1
            ],

            'rate_limit' => [
                'base_delay_ms' => 800,
                'max_delay_ms' => 5000,
                'retry' => 3
            ],

            'stages' => [

                [
                    'name' => 'list',
                    'parent' => 'center',
                    'fields' => [
                        'detail_url' => 'a.btn[href]'
                    ],
                    'next' => 'detail_url'
                ],

                [
                    'name' => 'detail',
                    'parent' => 'div.shop_area.shop_reverse',
                    'fields' => [
                        'company_name' => 'center h3[style*="color: #205871; text-transform: uppercase;"] b',
                        'address' => 'center h5',
                        'region' => 'center[style*="line-height: 12pt;"] h5',
                        'description' => '.company-description',
                        'business_type' => 'div.row:contains("Business Type") .col-sm-9',
                        'main_product' => 'div.row:contains("Main Product") .col-sm-9',
                        'year_established' => 'div.row:contains("Year of Establishment") .col-sm-9',
                        'scale' => 'div.row:contains("Scale of Business") .col-sm-9',
                        'email' => 'div.row:contains("Email") .col-sm-9',
                        'pic' => 'div.row:contains("PIC") .col-sm-9',
                        'telephone' => 'div.row:contains("Telephone") .col-sm-9',
                        'verify_date' => 'div.row:contains("Verify Date") .col-sm-9',
                        'category' => 'div.row:contains("Kategori Produk") .col-sm-9',
                    ]
                ]
            ]
        ],

        'kemenperin' => [

            'start_url' => 'https://kemenperin.go.id/direktori-eksportir?what=%20&prov=32',

            'pagination' => [
                'selector' => 'div.col-md-12.col-lg-12.col-xs-12.col-sm-12 center:first-of-type ul.pagination a[href]',
                'max_page' => 1
            ],

            'rate_limit' => [
                'base_delay_ms' => 800,
                'max_delay_ms' => 5000,
                'retry' => 3
            ],

            'stages' => [

                [
                    'name' => 'list',
                    'parent' => 'table#newspaper-a tbody tr[valign="top"]',  // ✅ Loop per TR
                    'fields' => [
                        'perusahaan' => 'td:nth-child(2) :text(1)',      // ✅ Relatif dari TR
                        'alamat' => 'td:nth-child(2) :text(2)',
                        'telp' => 'td:nth-child(2) :text(3):contains("Telp.")',
                        'email' => 'td:nth-child(2) :text(4):contains("e-Mail")',
                        'website' => 'td:nth-child(2) :text(4):contains("Website")',
                        'komoditi' => 'td:nth-child(3)',
                        'bidang_usaha' => 'td:nth-child(4)',
                    ]
                ]
            ]
        ],

        'pertanian' => [
            'file_path' => ['direktori-usaha-pertanian.html'],
            'stages' => [

                [
                    'name' => 'list',
                    'parent' => 'div#page-container div#pf1.pf.w0.h0 div.pc',
                    'fields' => [
                        'katalog' => 'div.t:nth-child(2)',
                        'katalogi' => 'div.t:nth-child(3)',
                        'katalogs' => 'div.t:nth-child(5)',
                    ]
                ],

                [
                    'name' => 'list',
                    'parent' => 'div#page-container div#pf2.pf.w0.h0 div.pc',
                    'fields' => [
                        'katalog1' => 'div.t:nth-child(2)',
                        'katalogi1' => 'div.t:nth-child(3)',
                        'katalogs1' => 'div.t:nth-child(5)',
                    ]
                ],

                [
                    'name' => 'list',
                    'parent' => 'div#page-container div#pf3.pf.w0.h0 div.pc',
                    'fields' => [
                        'katalog2' => 'div.t:nth-child(2)',
                        'katalogi2' => 'div.t:nth-child(3)',
                        'katalogs2' => 'div.t:nth-child(5)',
                    ]
                ]
            ]
        ],

    ];
}
