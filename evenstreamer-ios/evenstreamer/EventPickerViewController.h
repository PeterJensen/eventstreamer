//
//  DetailViewController.h
//  evenstreamer
//
//  Created by Stephan Lee on 1/23/14.
//  Copyright (c) 2014 Stephan Lee. All rights reserved.
//

#import <UIKit/UIKit.h>

@protocol EventPickerDelegate <NSObject>

- (void)onDimiss:(NSString *)eventName;

@end

@interface EventPickerViewController : UIViewController

@property (nonatomic, strong) id <EventPickerDelegate> delegate;

@end
