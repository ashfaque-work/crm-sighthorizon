<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $template = [

            [
                'template_name'=>'subject',
                'prompt'=>"Generate a lead subject line for a marketing campaign targeting potential customers for a software development company specializing in web and mobile applications.",
                'module'=>'lead',
                'field_json'=>'{"field":[{"label":"Description","placeholder":"e.g.","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'name',
                'prompt'=>"generate lead name for this lead description ##description##" ,
                'module'=>'lead',
                'field_json'=>'{"field":[{"label":"Lead Description","placeholder":"e.g.Marketing Power and Reliable Prospecting","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'name',
                'prompt'=>"generate deal name for this deal description ##description##" ,
                'module'=>'deal',
                'field_json'=>'{"field":[{"label":"Deal Description","placeholder":"e.g.Collaboration and Partnerships","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'subject',
                'prompt'=>"generate contract subject for this contract description ##description##",
                'module'=>'contract',
                'field_json'=>'{"field":[{"label":"Contract Description","placeholder":"e.g.Terms and Conditions","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'comment',
                'prompt'=>"generate strictly one line comment of ##title##",
                'module'=>'contractcomment',
                'field_json'=>'{"field":[{"label":"Contract Name","placeholder":"e.g. product return condition ","field_type":"textarea","field_name":"title"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'note',
                'prompt'=>"generate short and valuable note for contract title '##name##'",
                'module'=>'contractnote',
                'field_json'=>'{"field":[{"label":"Contract Name","placeholder":"e.g. product return condition ","field_type":"textarea","field_name":"name"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"generate contract brief description for title '##name##' and cover all point that suitable to contract title",
                'module'=>'contractdescription',
                'field_json'=>'{"field":[{"label":"Contract Name","placeholder":"e.g. product return condition ","field_type":"text_box","field_name":"name"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'name',
                'prompt'=>"generate contract name for this contract description ##description##",
                'module'=>'contract',
                'field_json'=>'{"field":[{"label":"Contract Description","placeholder":"e.g.write about your contract","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'notes',
                'prompt'=>"generate contract brief description for title '##name##' and cover all point that suitable to contract title",
                'module'=>'contract',
                'field_json'=>'{"field":[{"label":"Contract Name","placeholder":"e.g. product return condition ","field_type":"text_box","field_name":"name"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"Write a long creative product description for: ##title## \n\nTarget audience is: ##audience## \n\nUse this description: ##description## \n\nTone of generated text must be:\n ##tone_language## \n\n",
                'module'=>'productservice',
                'field_json'=>'{"field":[{"label":"Product Name","placeholder":"e.g. VR, Honda","field_type":"text_box","field_name":"title"},{"label":"Audience","placeholder":"e.g. Women, Aliens","field_type":"text_box","field_name":"audience"},{"label":"Product Description","placeholder":"e.g. VR is an innovative device that can allow you to be part of virtual world","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"generate short catchy description  for expense of ##description##",
                'module'=>'expense',
                'field_json'=>'{"field":[{"label":"Expense detail ","placeholder":"e.g. 12 computer","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'name',
                'prompt'=>"generate Contract Category name for this contract description ##description##",
                'module'=>'expense_categorie',
                'field_json'=>'{"field":[{"label":"Expense Category Description","placeholder":"e.g.write about your Expense Category","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"generate strictly one line description for a ##title##",
                'module'=>'expense_categorie',
                'field_json'=>'{"field":[{"label":"Title","placeholder":"e.g.","field_type":"text_box","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"generate valuable and short description of mdf for ##instructions##",
                'module'=>'mdf',
                'field_json'=>'{"field":[{"label":"Instructions","placeholder":"e.g.  ","field_type":"text_box","field_name":"instructions"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'title',
                'prompt'=>"Generate a list of Zoom meeting topics for ##description## meeting. The purpose of the meeting is to  ##description##. Structure the topics to ensure a productive discussion.",
                'module'=>'zoom meeting',
                'field_json'=>'{"field":[{"label":"Meeting Description ","placeholder":"e.g.Remote Collaboration","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'content',
                'prompt'=>"Generate a  notification message for an ##topic##.",
                'module'=>'notification template',
                'field_json'=>'{"field":[{"label":"Notification Topic ","placeholder":"e.g.new lead,new payment,new invoice","field_type":"textarea","field_name":"topic"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'meta_keywords',
                'prompt'=>"Write SEO meta title for:\n\n ##description## \n\nWebsite name is:\n ##title## \n\nSeed words:\n ##keywords## \n\n",
                'module'=>'seo',
                'field_json'=>'{"field":[{"label":"Website Name","placeholder":"e.g. Amazon, Google","field_type":"text_box","field_name":"title"},{"label":"Website Description","placeholder":"e.g. Describe what your website or business do","field_type":"textarea","field_name":"description"},{"label":"Keywords","placeholder":"e.g.  cloud services, databases","field_type":"text_box","field_name":"keywords"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'meta_description',
                'prompt'=>"Write SEO meta description for:\n\n ##description## \n\nWebsite name is:\n ##title## \n\nSeed words:\n ##keywords## \n\n",
                'module'=>'seo',
                'field_json'=>'{"field":[{"label":"Website Name","placeholder":"e.g. Amazon, Google","field_type":"text_box","field_name":"title"},{"label":"Website Description","placeholder":"e.g. Describe what your website or business do","field_type":"textarea","field_name":"description"},{"label":"Keywords","placeholder":"e.g.  cloud services, databases","field_type":"text_box","field_name":"keywords"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],[
                'template_name'=>'cookie_title',
                'prompt'=>"please suggest me cookie title for this ##description## website which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Website name or info","placeholder":"e.g. example website ","field_type":"textarea","field_name":"title"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],[
                'template_name'=>'cookie_description',
                'prompt'=>"please suggest me  Cookie description for this cookie title ##title##  which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Cookie Title ","placeholder":"e.g. example website ","field_type":"text_box","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'strictly_cookie_title',
                'prompt'=>"please suggest me only Strictly Cookie Title for this ##description## website which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Website name or info","placeholder":"e.g. example website ","field_type":"textarea","field_name":"title"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'strictly_cookie_description',
                'prompt'=>"please suggest me Strictly Cookie description for this Strictly cookie title ##title##  which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Strictly Cookie Title ","placeholder":"e.g. example website ","field_type":"text_box","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'more_information_description',
                'prompt'=>"I need assistance in crafting compelling content for my ##web_name## website's 'Contact Us' page of my website. The page should provide relevant information to users, encourage them to reach out for inquiries, support, and feedback, and reflect the unique value proposition of my business.",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Website Name","placeholder":"e.g. example website ","field_type":"text_box","field_name":"web_name"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'content',
                'prompt'=>"generate email template for ##type##",
                'module'=>'email template',
                'field_json'=>'{"field":[{"label":"Email Type","placeholder":"e.g. new user,new client","field_type":"text_box","field_name":"type"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'notes',
                'prompt'=>"Generate short description Note for lead ##description##",
                'module'=>'lead',
                'field_json'=>'{"field":[{"label":"Lead description","placeholder":"e.g. example website ","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'notes',
                'prompt'=>"Generate short description Note for deal ##description##",
                'module'=>'deal',
                'field_json'=>'{"field":[{"label":"Deal description","placeholder":"e.g.create note for deal client","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'call_result',
                'prompt'=>"Generate a short note summarizing the key points discussed during a lead ##name## call. The purpose of the note is to capture important details and action items discussed with the ##name## lead. Please structure the note in a concise and organized manner.",
                'module'=>'leadcall',
                'field_json'=>'{"field":[{"label":"Lead Name","placeholder":"e.g.  ","field_type":"textarea","field_name":"name"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'call_result',
                'prompt'=>"Generate a short note summarizing a deal call. Imagine you just had a call with a potential client or partner to discuss a ##description## deal. Write a concise summary of the key points discussed during the call. Include the important details such as the client's name, the purpose of the call, any agreements or decisions made, and next steps to be taken.",
                'module'=>'dealcall',
                'field_json'=>'{"field":[{"label":"Deal Name","placeholder":"e.g.  ","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"generate short description for a ##title##",
                'module'=>'leademail',
                'field_json'=>'{"field":[{"label":"Title","placeholder":"e.g.","field_type":"textarea","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"generate short description for a ##title##",
                'module'=>'dealemail',
                'field_json'=>'{"field":[{"label":"Title","placeholder":"e.g.","field_type":"textarea","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'terms',
                'prompt'=>"Generate short terms and condition for estimation subject ##subject##",
                'module'=>'estimation',
                'field_json'=>'{"field":[{"label":"write the Subject of Estimation?","placeholder":"e.g. write the subject of your estimation","field_type":"textarea","field_name":"subject"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'terms',
                'prompt'=>"Generate short terms and condition for invoice subject ##subject##",
                'module'=>'invoice',
                'field_json'=>'{"field":[{"label":"write the Subject of Invoice?","placeholder":"e.g. write the subject of your invoice","field_type":"textarea","field_name":"subject"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

        ];
        Template::insert($template);
    }
}
