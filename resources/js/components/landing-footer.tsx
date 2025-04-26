export function LandingFooter () {
    return (
        <footer className="p-4 bg-[var(--background)] md:p-8 lg:p-10  w-full">
            <div className="mx-auto max-w-screen-xl text-center">
                <a href="#"
                   className="flex justify-center items-center text-2xl font-semibold text-gray-900 dark:text-white gap-2">
                    <img
                        src="/images/3.png"
                        alt="betterbloq logo"
                        className="h-10 w-auto rounded-md"
                    />
                    Betterbloq
                </a>
                <p className="my-6 text-gray-500 dark:text-gray-400">Tools for modern real estate development.</p>
                <ul className="flex flex-wrap justify-center items-center mb-6 text-gray-900 dark:text-white">
                    <li>
                        <a href="#" className="mr-4 hover:underline md:mr-6 ">About</a>
                    </li>
                    <li>
                        <a href="#" className="mr-4 hover:underline md:mr-6">Premium</a>
                    </li>
                    <li>
                        <a href="#" className="mr-4 hover:underline md:mr-6 ">Campaigns</a>
                    </li>
                    <li>
                        <a href="#" className="mr-4 hover:underline md:mr-6">Blog</a>
                    </li>
                    <li>
                        <a href="#" className="mr-4 hover:underline md:mr-6">Affiliate Program</a>
                    </li>
                    <li>
                        <a href="#" className="mr-4 hover:underline md:mr-6">FAQs</a>
                    </li>
                    <li>
                        <a href="#" className="mr-4 hover:underline md:mr-6">Contact</a>
                    </li>
                </ul>
                <span className="text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2025 <a href="#"
                                                                                                    className="hover:underline">Betterbloq™</a>. All Rights Reserved.</span>
            </div>
        </footer>
    )
}
