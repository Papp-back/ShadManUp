<?php



return [
    'login' => [
        'messages' => [
            'mobile' => 'شماره موبایل را وارد کنید',
            'password' => 'رمز عبور را وارد کنید',
        ],
        'rules' => [
            'mobile' => 'required',
            'password' => 'required',
        ],
    ],
    'Adminlogin' => [
        'messages' => [
            'mobile' => 'شماره موبایل را وارد کنید',
        ],
        'rules' => [
            'mobile' => 'required',
        ],
    ],
    'verify' => [
        'messages' => [
            'mobile' => 'شماره موبایل را وارد کنید',
            'code' => 'کد ارسالی را وارد کنید',
        ],
        'rules' => [
            'mobile' => 'required',
            'code' => 'required',
        ],
    ],
    'userReferral' => [
        'messages' => [
            'referral_code' => 'کد معرف را وارد کنید',
        ],
        'rules' => [
            'referral_code' => 'nullable',
        ],
    ],
    'saveAvatar' => [
        'messages' => [
            'avatar.required' => ' تصویر را وارد کنید',
            'avatar.image' => 'فرمت تصویر باید باشد',
            'avatar.mimes' => 'تصویر باید یکی از فرمت های jpeg,png,jpg باشد .',
            'avatar.max' => 'تصویر باید کتر از 2mb باشد',
        ],
        'rules' => [
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ],
    ],
    'updateUserData' => [
        'messages' => [
            'firstname.required' => ' نام را وارد کنید',
            'lastname.required' => ' نام خانوادگی را وارد کنید',
            'national_code.required' => ' کد ملی را وارد کنید',
        ],
        'rules' => [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'national_code' => 'required|string|max:255',
        ],
    ],
    'StoreCategory' => [
        'messages' => [
            'parent_id' => 'شناسه والد را وارد کنید',
            'name.required' => 'نام دسته بندی را وارد کنید.',
            'name.unique' => 'دسته بندی با این نام وجود دارد.',
        ],
        'rules' => [
            'parent_id' => 'required',
            'name' => 'required|unique:categories,name',
        ],
    ],
    'StoreCourse' => [
        'messages' => [
            'title.required' => 'عنوان دوره را وارد کنید.',
            'category_id.required' => 'شناسه دسته بندی را وارد کنید.',
            'category_id.integer' => 'شناسه دسته بندی باید عددی باشد.',
            'category_id.exists' => 'دسته بندی وارد شده معتبر نیست.',
            'author.required' => 'نام نویسنده را وارد کنید.',
            'description.required' => 'توضیحات دوره را وارد کنید.',
            'price.required' => 'قیمت دوره را وارد کنید.',
            'price.numeric' => 'قیمت دوره باید عددی باشد.',
            'discount.numeric' => 'تخفیف دوره باید عددی باشد.',
            'image.required' => 'تصویر دوره را آپلود کنید.',
            'image.image' => 'فایل باید یک تصویر باشد.',
            'image.mimes' => 'فرمت تصویر باید jpeg، png، jpg یا gif باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
        ],
        'rules' => [
            'title' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
            'author' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],
    ],
    'updateCourseImage' => [
        'messages' => [
            'image.required' => 'تصویر دوره را آپلود کنید.',
            'image.image' => 'فایل باید یک تصویر باشد.',
            'image.mimes' => 'فرمت تصویر باید jpeg، png، jpg یا gif باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
        ],
        'rules' => [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],
    ],
    'updateCourseFile' => [
        'messages' => [
            'file.required' => 'فایل را آپلود کنید.',
            'file.max' => 'حجم تصویر نباید بیشتر از 700 مگابایت باشد.',
        ],
        'rules' => [
            'file' => 'required|max:716800',
        ],
    ],
    'updateCourse' => [
        'messages' => [
            'title.required' => 'عنوان دوره را وارد کنید.',
            'category_id.required' => 'شناسه دسته بندی را وارد کنید.',
            'category_id.integer' => 'شناسه دسته بندی باید عددی باشد.',
            'category_id.exists' => 'دسته بندی وارد شده معتبر نیست.',
            'author.required' => 'نام نویسنده را وارد کنید.',
            'description.required' => 'توضیحات دوره را وارد کنید.',
            'price.required' => 'قیمت دوره را وارد کنید.',
            'price.numeric' => 'قیمت دوره باید عددی باشد.',
            'discount.numeric' => 'تخفیف دوره باید عددی باشد.',
        ],
        'rules' => [
            'title' => 'required|string',
            'hours' => 'required|numeric',
            'category_id' => 'required|integer|exists:categories,id',
            'author' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
        ],
    ],
    'StoreSessionCourse' => [
        'messages' => [
            'title.required' => 'عنوان جلسه را وارد کنید.',
            'title.string' => 'عنوان جلسه باید از نوع رشته باشد.',
            'course_section_id.required' => 'شناسه بخش دوره را وارد کنید.',
            'course_section_id.integer' => 'شناسه دوره باید از نوع عدد صحیح باشد.',
            'course_section_id.exists' => 'دوره با این شناسه وجود ندارد.',
            'duration_minutes.required' => 'مدت زمان دوره را وارد کنید.',
        ],
        'rules' => [
            'title' => 'required|string',
            'description' => 'nullable',
            'course_section_id' => 'required|integer|exists:course_sections,id',
            'duration_minutes' => 'required|integer',
            'file_url' => 'nullable',
        ],
    ],
    'StoreSectionCourse' => [
        'messages' => [
            'title.required' => 'عنوان بخش دوره را وارد کنید.',
            'title.string' => 'عنوان بخش دوره باید از نوع رشته باشد.',
            'course_id.required' => 'شناسه دوره را وارد کنید.',
            'course_id.integer' => 'شناسه دوره باید از نوع عدد صحیح باشد.',
            'course_id.exists' => 'دوره با این شناسه وجود ندارد.',
            'price.required' => 'قیمت بخش دوره را وارد کنید.',
            'price.numeric' => 'قیمت بخش دوره باید عددی باشد.',
            'discount.numeric' => 'تخفیف بخش دوره باید عددی باشد.',
        ],
        'rules' => [
            'title' => 'required|string',
            'description' => 'nullable',
            'course_id' => 'required|integer|exists:courses,id',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            
        ],
    ],
    'StoreCommentCourse' => [
        'messages' => [
            'title.required' => 'عنوان بخش دوره را وارد کنید.',
            'title.string' => 'عنوان بخش دوره باید از نوع رشته باشد.',
            'course_id.required' => 'شناسه دوره را وارد کنید.',
            'course_id.integer' => 'شناسه دوره باید از نوع عدد صحیح باشد.',
            'course_id.exists' => 'دوره با این شناسه وجود ندارد.',
            'price.required' => 'قیمت بخش دوره را وارد کنید.',
            'price.numeric' => 'قیمت بخش دوره باید عددی باشد.',
            'discount.numeric' => 'تخفیف بخش دوره باید عددی باشد.',
        ],
        'rules' => [
            'title' => 'required|string',
            'description' => 'nullable',
            'course_id' => 'required|integer|exists:courses,id',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            
        ],
    ],
    'StoreNotification' => [
        'messages' => [
            'user_id.required' => 'شناسه کاربر را وارد کنید.',
            'user_id.integer' => 'شناسه کاربر باید از نوع عدد صحیح باشد.',
            'title.required' => 'عنوان پیام را وارد کنید.',
            'content.required' => 'متن پیام را وارد کنید.',
            'content.string' => 'عنوان پیام باید از نوع رشته باشد.',
            'title.string' => 'متن پیام باید از نوع رشته باشد.',
        ],
        'rules' => [
            'user_id' => 'required|integer',
            'title' => 'required|string',
            'content' => 'required|string',
            'read'=>'nullable'
            
        ],
    ],
    
];