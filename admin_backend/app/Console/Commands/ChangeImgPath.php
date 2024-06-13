<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Discount;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Referral;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Console\Command;

class ChangeImgPath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:changeImgPath';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Banner::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Blog::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Brand::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Category::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Coupon::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Discount::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Product::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Recipe::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'image' => config('app.img_host').$img->image
                ]);
            }
        } );

        Review::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img
                ]);
            }
        } );

        Shop::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'logo_img' => config('app.img_host').$img->logo_img,
                    'background_img' => config('app.img_host').$img->background_img,
                    'adhar' => config('app.img_host').$img->adhar
                ]);
            }
        } );

        User::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'img' => config('app.img_host').$img->img,
                ]);
            }
        } );

        Gallery::chunk(1000, function ($images){
            foreach ($images as $img){
                $img->update([
                    'path' => config('app.img_host').$img->path,
                ]);
            }
        });
    }
}
