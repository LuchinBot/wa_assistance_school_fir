<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Security\User;
use App\Models\Product;
use App\Models\Param;

class HomeController extends Controller
{
    public $extend = null;

    public function __construct()
    {
        $this->extend = [
            'title' => 'Inicio',
            'controller' => 'home',
        ];
    }
    public function index()
    {
        $data = null;
        $section = 0;
        return view('welcome', array_merge(['data' => $data], ['section' => $section], ['extend' => $this->extend] ));
    }

    public function menu()
    {
        // Data de ejemplo - estructura de menú
        $lineas = [
            [
                'id' => 1,
                'nombre' => 'Bebidas',
                'icono' => 'glass-martini',
                'slug' => 'bebidas',
                'categorias' => [
                    [
                        'id' => 1,
                        'nombre' => 'Licores Premium',
                        'productos' => [
                            [
                                'id' => 1,
                                'marca' => 'Johnnie Walker',
                                'nombre' => 'Black Label 750 ML',
                                'descripcion' => 'Johnnie Walker Black Label es una mezcla maestra elaborada con whiskies madurados durante al menos 12 años de las cuatro regiones principales de Escocia',
                                'imagen' => 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=400',
                                'badge' => 'Premium'
                            ],
                            [
                                'id' => 2,
                                'marca' => 'Chivas Regal',
                                'nombre' => '12 Años 750 ML',
                                'descripcion' => 'Un whisky escocés mezclado suave y sofisticado con notas de miel, vainilla y manzanas maduras',
                                'imagen' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=400',
                                'badge' => null
                            ],
                            [
                                'id' => 3,
                                'marca' => 'Bacardi',
                                'nombre' => 'Reserva Ocho 750 ML',
                                'descripcion' => 'Ron dorado premium envejecido bajo el sol del Caribe durante un mínimo de ocho años',
                                'imagen' => 'https://images.unsplash.com/photo-1560508020-27e2c9ae3f14?w=400',
                                'badge' => 'Nuevo'
                            ]
                        ]
                    ],
                    [
                        'id' => 2,
                        'nombre' => 'Cervezas',
                        'productos' => [
                            [
                                'id' => 4,
                                'marca' => 'Corona',
                                'nombre' => 'Extra 355 ML',
                                'descripcion' => 'Cerveza lager mexicana de sabor ligero y refrescante, perfecta para cualquier ocasión',
                                'imagen' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=400',
                                'badge' => null
                            ],
                            [
                                'id' => 5,
                                'marca' => 'Heineken',
                                'nombre' => 'Lager 330 ML',
                                'descripcion' => 'Cerveza premium con un sabor equilibrado y refrescante, reconocida mundialmente',
                                'imagen' => 'https://images.unsplash.com/photo-1618885472179-5e474019f2a9?w=400',
                                'badge' => null
                            ]
                        ]
                    ],
                    [
                        'id' => 3,
                        'nombre' => 'Vinos',
                        'productos' => [
                            [
                                'id' => 6,
                                'marca' => 'Casillero del Diablo',
                                'nombre' => 'Cabernet Sauvignon 750 ML',
                                'descripcion' => 'Vino tinto chileno con cuerpo medio, notas de frutas rojas y un final suave',
                                'imagen' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400',
                                'badge' => 'Recomendado'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'id' => 2,
                'nombre' => 'Alimentos',
                'icono' => 'utensils',
                'slug' => 'alimentos',
                'categorias' => [
                    [
                        'id' => 4,
                        'nombre' => 'Snacks',
                        'productos' => [
                            [
                                'id' => 7,
                                'marca' => 'Premium Snacks',
                                'nombre' => 'Mix de Frutos Secos',
                                'descripcion' => 'Selección premium de almendras, nueces y pistachos perfectamente tostados',
                                'imagen' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=400',
                                'badge' => null
                            ],
                            [
                                'id' => 8,
                                'marca' => 'Gourmet',
                                'nombre' => 'Papas Artesanales 200g',
                                'descripcion' => 'Papas fritas artesanales con sal marina y hierbas naturales',
                                'imagen' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400',
                                'badge' => 'Nuevo'
                            ]
                        ]
                    ],
                    [
                        'id' => 5,
                        'nombre' => 'Entradas',
                        'productos' => [
                            [
                                'id' => 9,
                                'marca' => 'Chef Special',
                                'nombre' => 'Tabla de Quesos',
                                'descripcion' => 'Selección de quesos artesanales acompañados de mermeladas y frutos secos',
                                'imagen' => 'https://images.unsplash.com/photo-1452195100486-9cc805987862?w=400',
                                'badge' => 'Popular'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'id' => 3,
                'nombre' => 'Postres',
                'icono' => 'birthday-cake',
                'slug' => 'postres',
                'categorias' => [
                    [
                        'id' => 6,
                        'nombre' => 'Repostería',
                        'productos' => [
                            [
                                'id' => 10,
                                'marca' => 'Dulce Arte',
                                'nombre' => 'Cheesecake de Frutos Rojos',
                                'descripcion' => 'Delicioso cheesecake cremoso con coulis de frutos rojos naturales',
                                'imagen' => 'https://images.unsplash.com/photo-1533134486753-c833f0ed4866?w=400',
                                'badge' => 'Especial'
                            ],
                            [
                                'id' => 11,
                                'marca' => 'Dulce Arte',
                                'nombre' => 'Brownie con Helado',
                                'descripcion' => 'Brownie de chocolate caliente con helado de vainilla y salsa de chocolate',
                                'imagen' => 'https://images.unsplash.com/photo-1607920591413-4ec007e70023?w=400',
                                'badge' => null
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Convertir a objeto para usar en Blade como objetos
        $data = json_decode(json_encode($lineas));

        $section = 0;

        return view('menu', [
            'lineas' => $data,
            'section' => $section,
            'extend' => $this->extend
        ]);
    }
}
